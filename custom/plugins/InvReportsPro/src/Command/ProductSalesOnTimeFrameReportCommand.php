<?php declare(strict_types=1);

namespace InvReportsPro\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This is a temporary command to fulfill the customers request; will be replaced by a configurable command eventually
 * @ticket #104
 *
 * Class ProductSalesOnTimeFrameReportCommand
 * @package InvReportsPro\Command
 */
class ProductSalesOnTimeFrameReportCommand extends Command
{


    /**
     * @var string
     */
    protected static $defaultName = 'inv:reports-pro:product-sales-on-timeframe';


    /**
     * @var Connection
     */
    private $dbConnection;

    /**
     * @var string
     */
    private $storageDirectory;

    /**
     * @param Connection $dbConnection
     * @return ProductSalesOnTimeFrameReportCommand
     */
    public function setDbConnection(Connection $dbConnection): ProductSalesOnTimeFrameReportCommand
    {
        $this->dbConnection = $dbConnection;
        return $this;
    }

    /**
     * @param string $storageDirectory
     * @return ProductSalesOnTimeFrameReportCommand
     */
    public function setStorageDirectory(string $storageDirectory): ProductSalesOnTimeFrameReportCommand
    {
        $this->storageDirectory = $storageDirectory;
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->addOption(
            'dateFrom',
            'df',
            InputOption::VALUE_OPTIONAL,
            'Date to include orders from',
            '2020-06-01'
        );

        $this->addOption(
            'dateTo',
            'dt',
            InputOption::VALUE_OPTIONAL,
            'Date to include orders to',
            '2020-08-31'
        );

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $dateFrom = new \DateTime($input->getOption('dateFrom'));
        $dateTo = new \DateTime($input->getOption('dateTo'));

        $rows = $this->fetchData($dateFrom, $dateTo);
        $generatedFile = $this->writeCsv($dateFrom, $dateTo, $rows, $output);
        $output->writeln('Generated file ' . $generatedFile->getPathname());
    }

    /**
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function fetchData(\DateTime $dateFrom, \DateTime $dateTo): array
    {
        $this->dbConnection->query(
            <<<SQL
CREATE TEMPORARY TABLE inv_report_order_state_view AS
SELECT o.id,
       o.auto_increment,
       o.order_number,
       o.version_id,
       HEX(o.id)          as id_hr,
       HEX(o.state_id)    as state_id_hr,
       sms.technical_name as state_technical_name,
       o.created_at,
       ot_paid.updated_at as paid_at
FROM `order` as o
         LEFT JOIN order_transaction ot_paid on o.id = ot_paid.order_id and o.version_id = ot_paid.order_version_id AND
                                                ot_paid.state_id = UNHEX('67BB219D99544376A0B13A235FFFCA1C')
         LEFT JOIN state_machine_state sms on o.state_id = sms.id;
SQL
        );

        // Varianten
        $statement = $this->dbConnection->prepare(
            <<<SQL
        SELECT HEX(p.id),
       order_line_item.type as type,
       order_line_item.label as name,
       p.product_number,
       SUM(quantity) as sum_quantity,

       JSON_EXTRACT(order_line_item.price ->> '$.unitPrice', '$[0]') as gross_price,
       JSON_EXTRACT(order_line_item.price ->> '$.calculatedTaxes[0]', '$.taxRate') as tax_rate,

       CAST(JSON_EXTRACT(order_line_item.price ->> '$.unitPrice', '$[0]') AS decimal(7, 2)) / (JSON_EXTRACT(order_line_item.price ->> '$.calculatedTaxes[0]', '$.taxRate') / 100 + 1) as net_price,

       CAST(JSON_EXTRACT(order_line_item.price ->> '$.unitPrice', '$[0]') AS decimal(7, 2)) / (JSON_EXTRACT(order_line_item.price ->> '$.calculatedTaxes[0]', '$.taxRate') / 100 + 1) *
       SUM(quantity) as net_price_total,


       p.purchase_unit as weight_single,
      SUM(quantity) * p.purchase_unit as weight_sum

FROM order_line_item
         LEFT JOIN product p on order_line_item.product_id = p.id
         LEFT JOIN product_translation pt on p.id = pt.product_id
         LEFT join inv_report_order_state_view on order_line_item.order_id = inv_report_order_state_view.id AND
                                                  order_line_item.order_version_id =
                                                      inv_report_order_state_view.version_id
WHERE p.id IS NOT NULL
        AND order_line_item.product_id IS NOT NULL
        AND order_line_item.type = 'product'
        AND inv_report_order_state_view.state_technical_name = 'in_progress'
        AND (inv_report_order_state_view.paid_at > :date_gt AND
        inv_report_order_state_view.paid_at < :date_lt)
GROUP BY p.product_number, order_line_item.label, HEX(p.id), p.purchase_unit
ORDER BY net_price_total DESC;

SQL

        );

        $statement->execute(
            [
                'date_gt' => $dateFrom->format('Y-m-d H:i:is'),
                'date_lt' => $dateTo->format('Y-m-d H:i:is')
            ]
        );
        $rows = $statement->fetchAll();

        return array_map(
            function ($row) {
                return [
                    'Name' => $row['name'],
                    'SKU' => $row['product_number'],
                    'Steuersatz' => $row['tax_rate'],
                    'Einzelpreis (brutto)' => $row['gross_price'],
                    'Einzelpreis (netto)' => $row['net_price'],
                    'Bestellungen' => $row['sum_quantity'],
                    'Umsatz (netto) gesamt' => $row['net_price_total']
                ];
            }, $rows
        );
    }

    /**
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @param array $rows
     * @param OutputInterface $output
     * @return \SplFileObject
     */
    protected function writeCsv(
        \DateTime $dateFrom,
        \DateTime $dateTo,
        array $rows,
        OutputInterface $output
    ): \SplFileObject {
        if (!is_dir($this->storageDirectory)) {
            mkdir($this->storageDirectory, 0777);
        }
        $fileName = sprintf(
            'Report.Sales.Product.%s.%s.csv',
            $dateFrom->format('Y_m_d__H_i_s'),
            $dateTo->format('Y_m_d__H_i_s')
        );

        $df = fopen($this->storageDirectory . DIRECTORY_SEPARATOR . $fileName, 'w');
        fputcsv($df, array_keys(reset($rows)));
        foreach ($rows as $row) {
            fputcsv($df, $row);
        }
        fclose($df);

        return new \SplFileObject($this->storageDirectory . DIRECTORY_SEPARATOR . $fileName);
    }


}




