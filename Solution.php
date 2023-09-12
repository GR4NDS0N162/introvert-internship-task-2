<?php

namespace Task;

use DateInterval;
use DateTime;
use Introvert\ApiClient;
use Introvert\ApiException;
use Introvert\Configuration;

class Solution
{
    /**
     * @var Configuration
     */
    private Configuration $configuration;

    /**
     * @var ApiClient
     */
    private ApiClient $api;

    /**
     * @param string $host
     * @param string $key
     */
    public function __construct(string $host, string $key)
    {
        $this->configuration = Configuration::getDefaultConfiguration();
        $this->configuration->setHost($host);
        $this->configuration->setApiKey('key', $key);
        $this->api = new ApiClient();
    }

    /**
     * @param int $dateFieldId
     * @param int[] $statusLeadIds
     *
     * @return array
     */
    public function calculateCounts(int $dateFieldId, array $statusLeadIds): array
    {
        if (!$this->hasAccess($this->api)) {
            return ['message' => 'Клиент не имеет доступ в amoCRM.'];
        }

        $counts = $this->initCounts();

        try {
            $count = 250;
            $offset = 0;

            do {
                $result = $this->api->lead->getAll(status: $statusLeadIds, count: $count, offset: $offset);

                foreach ($result['result'] as $lead) {
                    foreach ($lead['custom_fields'] as $customField) {
                        if ($customField['id'] == $dateFieldId) {
                            $date = date('Y-m-d', strtotime($customField['values'][0]['value']));
                            if (isset($counts[$date])) {
                                $counts[$date]++;
                            }
                        }
                    }
                }

                $offset += $count;
            } while ($result['count'] > 0);
        } catch (ApiException $e) {
            return ['message' => 'Exception when calling lead->getAll: ' . $e->getMessage()];
        }

        return $counts;
    }

    /**
     * @param ApiClient $api
     *
     * @return bool
     */
    private function hasAccess(ApiClient $api): bool
    {
        try {
            $api->account->info();
            return true;
        } catch (ApiException) {
            return false;
        }
    }

    /**
     * @return array
     */
    private function initCounts(): array
    {
        $count = [];

        $date = new DateTime();
        $day = new DateInterval('P1D');
        for ($i = 0; $i < 30; $i++) {
            $count[$date->format('Y-m-d')] = 0;
            $date->add($day);
        }

        return $count;
    }
}
