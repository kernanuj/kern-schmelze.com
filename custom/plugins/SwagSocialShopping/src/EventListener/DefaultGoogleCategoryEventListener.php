<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagSocialShopping\EventListener;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\InsertCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\UpdateCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Validation\PreWriteValidationEvent;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\WriteConstraintViolationException;
use SwagSocialShopping\Component\Network\Facebook;
use SwagSocialShopping\Component\Network\GoogleShopping;
use SwagSocialShopping\Component\Network\Instagram;
use SwagSocialShopping\DataAbstractionLayer\Entity\SocialShoppingSalesChannelDefinition;
use SwagSocialShopping\DataAbstractionLayer\Entity\SocialShoppingSalesChannelEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class DefaultGoogleCategoryEventListener implements EventSubscriberInterface
{
    /**
     * @var EntityRepositoryInterface
     */
    private $socialShoppingSalesChannelRepository;

    public function __construct(EntityRepositoryInterface $socialShoppingSalesChannelRepository)
    {
        $this->socialShoppingSalesChannelRepository = $socialShoppingSalesChannelRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PreWriteValidationEvent::class => 'preValidate',
        ];
    }

    public function preValidate(PreWriteValidationEvent $event): void
    {
        $violations = new ConstraintViolationList();

        foreach ($event->getCommands() as $command) {
            if ((!($command instanceof InsertCommand) && !($command instanceof UpdateCommand))
                || $command->getDefinition()->getClass() !== SocialShoppingSalesChannelDefinition::class
            ) {
                continue;
            }

            $payload = $command->getPayload();
            if (!isset($payload['configuration'])) {
                continue;
            }

            $network = $this->getNetwork(
                $payload,
                Uuid::fromBytesToHex($command->getPrimaryKey()['id']),
                $event->getContext()
            );
            if (!\in_array($network, [Facebook::class, Instagram::class, GoogleShopping::class], true)) {
                continue;
            }

            $configuration = \json_decode($payload['configuration'], true);
            if (!isset($configuration['defaultGoogleProductCategory'])
                || $configuration['defaultGoogleProductCategory'] <= 0
            ) {
                $violations->add(
                    $this->buildViolation(
                        'The network needs a default google product category',
                        [],
                        'configuration.defaultGoogleProductCategory'
                    )
                );
            }
        }

        if ($violations->count() > 0) {
            $event->getExceptions()->add(new WriteConstraintViolationException($violations));
        }
    }

    private function getNetwork(array $payload, string $primaryKey, Context $context): string
    {
        if (isset($payload['network'])) {
            return $payload['network'];
        }

        $socialShoppingSalesChannel = $this->socialShoppingSalesChannelRepository->search(
            new Criteria([$primaryKey]),
            $context
        )->get($primaryKey);

        if ($socialShoppingSalesChannel instanceof SocialShoppingSalesChannelEntity) {
            return $socialShoppingSalesChannel->getNetwork();
        }

        throw new \RuntimeException('Network not specified');
    }

    private function buildViolation(
        string $messageTemplate,
        array $parameters,
        ?string $root = null
    ): ConstraintViolationInterface {
        return new ConstraintViolation(
            \str_replace(\array_keys($parameters), \array_values($parameters), $messageTemplate),
            $messageTemplate,
            $parameters,
            $root,
            null,
            null
        );
    }
}
