<?php

namespace Tests\YandexCheckout\Request\Refunds;

use PHPUnit\Framework\TestCase;
use YandexCheckout\Model\RefundStatus;
use YandexCheckout\Request\Refunds\RefundsRequest;
use YandexCheckout\Request\Refunds\RefundsRequestSerializer;

class RefundsRequestSerializerTest extends TestCase
{
    private $fieldMap = array(
        'paymentId'      => 'payment_id',
        'createdAtGte'   => 'created_at.gte',
        'createdAtGt'    => 'created_at.gt',
        'createdAtLte'   => 'created_at.lte',
        'createdAtLt'    => 'created_at.lt',
        'status'         => 'status',
        'cursor'         => 'cursor',
        'limit'          => 'limit',
    );

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSerialize($options)
    {
        $serializer = new RefundsRequestSerializer();
        $data = $serializer->serialize(RefundsRequest::builder()->build($options));

        $expected = array();
        foreach ($this->fieldMap as $field => $mapped) {
            if (isset($options[$field])) {
                $value = $options[$field];
                if (!empty($value)) {
                    $expected[$mapped] = $value instanceof \DateTime ? $value->format(DATE_ATOM) : $value;
                }
            }
        }
        self::assertEquals($expected, $data);
    }

    public function validDataProvider()
    {
        $result = array(
            array(
                array(
                    'accountId' => uniqid(),
                ),
            ),
            array(
                array(
                    'paymentId' => '',
                    'createAtGte' => '',
                    'createAtGt' => '',
                    'createAtLte' => '',
                    'createAtLt' => '',
                    'status' => '',
                    'cursor' => '',
                    'limit' => '',
                ),
            ),
        );
        $statuses = RefundStatus::getValidValues();
        for ($i = 0; $i < 10; $i++) {
            $request = array(
                'paymentId' => $this->randomString(36),
                'createAtGte' => date(DATE_ATOM, mt_rand(1, time())),
                'createAtGt' => date(DATE_ATOM, mt_rand(1, time())),
                'createAtLte' => date(DATE_ATOM, mt_rand(1, time())),
                'createAtLt' => date(DATE_ATOM, mt_rand(1, time())),
                'status' => $statuses[mt_rand(0, count($statuses) - 1)],
                'cursor' => uniqid(),
                'limit' => mt_rand(1, RefundsRequest::MAX_LIMIT_VALUE),
            );
            $result[] = array($request);
        }
        return $result;
    }

    private function randomString($length, $any = true)
    {
        static $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-+_.';

        $result = '';
        for ($i = 0; $i < $length; $i++) {
            if ($any) {
                $char = chr(mt_rand(32, 126));
            } else {
                $rnd = mt_rand(0, strlen($chars) - 1);
                $char = substr($chars, $rnd, 1);
            }
            $result .= $char;
        }
        return $result;
    }
}