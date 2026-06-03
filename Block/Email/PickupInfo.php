<?php

declare(strict_types=1);

namespace ETechFlow\InStorePickup\Block\Email;

use ETechFlow\InStorePickup\Api\StoreRepositoryInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Renders the "Collection Details" panel inside the order confirmation
 * email. Invoked from the email template via:
 *
 *   {{block class="ETechFlow\InStorePickup\Block\Email\PickupInfo"
 *           area="frontend"
 *           template="ETechFlow_InStorePickup::email/pickup-info.phtml"
 *           order_id=$order_id}}
 *
 * Returns null (template renders nothing) for non-pickup orders, so it
 * is safe to include unconditionally.
 */
class PickupInfo extends Template
{
    /** @var string */
    protected $_template = 'ETechFlow_InStorePickup::email/pickup-info.phtml';

    public function __construct(
        Context $context,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly StoreRepositoryInterface $storeRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return array{store_name:string,address:string,phone:string,instructions:string,pretty:string}|null
     */
    public function getPickupInfo(): ?array
    {
        $order = $this->getData('order');
        if (!$order) {
            $orderId = (int) $this->getData('order_id');
            if ($orderId <= 0) {
                return null;
            }
            try {
                $order = $this->orderRepository->get($orderId);
            } catch (\Throwable $e) {
                return null;
            }
        }

        $storeId  = (int) $order->getData('etechflow_isp_pickup_store_id');
        $pickupAt = (string) $order->getData('etechflow_isp_pickup_at');
        if ($storeId <= 0 || $pickupAt === '') {
            return null;
        }

        try {
            $store = $this->storeRepository->getById($storeId);
        } catch (\Throwable $e) {
            return null;
        }

        $address = trim(implode(', ', array_filter([
            (string) $store->getStreet(),
            (string) $store->getCity(),
            (string) $store->getPostcode(),
            (string) $store->getCountryCode(),
        ])));

        return [
            'store_name'   => (string) $store->getName(),
            'address'      => $address,
            'phone'        => (string) $store->getPhone(),
            'instructions' => (string) $store->getPickupInstructions(),
            'pretty'       => $this->formatPretty($pickupAt),
        ];
    }

    private function formatPretty(string $dt): string
    {
        try {
            $d = new \DateTime($dt);
            return $d->format('l j F Y') . ' at ' . $d->format('g:i A');
        } catch (\Throwable $e) {
            return $dt;
        }
    }
}
