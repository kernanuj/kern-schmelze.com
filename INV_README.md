
# Plugins
## InvExportLabel

### 

1. Create cron job running once a day to export labels, example
>bin/console inv:export-label:export --daysBack=1 --daysForward=0 --type=inv_mixer_product --includeInvoice=1 --updateStatus=1
>bin/console inv:export-label:export --daysBack=10 --daysForward=1 --type=inv_mixer_product

Aktuell Live: 
cd kern-schmelze.com/kern-schmelze.com; "$(ls -td release-* | head -1)"/bin/console inv:export-label:export --daysBack=365 --daysForward=1 --type=inv_mixer_product --includeInvoice=1 --updateStatus=1

#### orders considered in cron job

For an order to be considered in the export, the following must match
- Order must contain at least one item of the defined type; for now it is only InvMixerProductItem
- Order must have a transaction update (paid etc) within the timeframe given as arguments to the command (-1day to + 2 days for example)
- Order must match a configured state combination; the configuration is done in the plugin settings. Each state combination consists of
    - order state (the overall order)
    - transaction state (payment)
    - delivery state
    - example : `order:open, transaction:paid, delivery:open`


#Plugins 
## InvReportsPro
Die Version ist nur für den Gebrauch mit diesem Shop nutzbar; für eine Verbreitung per Plugin ist ein weitaus umfassenderer Ansatz notwendig
Einige Produkte haben in der Datenbank keine Translation mehr verknüpft; daher wird das Label aus dem order_line_items verwendet; da nach diesem auch in der query gruppiert wird, ist es möglich dass eine Produktnummer 2 x aufgeführt wird
Die meisten Produkte haben kein Gewicht definiert; daher wird genrell die purchase_unit verwendet und es wird davon ausgegangen, dass diese immer in g angegeben ist

bin/console inv:reports-pro:product-sales-on-timeframe --dateFrom=2020-09-01 --dateTo=2020-12-31
