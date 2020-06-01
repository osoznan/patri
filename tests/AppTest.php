<?php
namespace app\spec\system;

use osoznan\patri\Top;


function testConfig() {
    return [
        'components' => [
            'mailer' => [
                'class' => 'app\components\MailSender',
                'on' => [
                    'success' => function () {
                        echo 'ok';
                    },
                    'error' => function () {
                        echo 'error';
                    }
                ]
            ]
        ]
    ];
}

describe('App', function() {
    beforeEach(function() {
    });

    it('getConfig', function() {
        Top::$app->config = testConfig();

        $res1 = Top::$app->getConfig('components');
        expect(empty($res1['mailer']))->toBeFalsy();

        $res2 = Top::$app->getConfig('components.mailer');
        expect(empty($res2))->toBeFalsy();

        $res = Top::$app->getConfig('components.wrong_param');
        expect($res)->toBeNull();

    });

    it('get', function() {
        Top::$app->config = testConfig();

        $res = Top::$app->get('wrong component id');
        expect($res)->toBeNull();

        $res = Top::$app->get('mailer');
        expect(get_class($res))->toEqual(MailSender::class);

        expect(is_array($handler = $res->getHandler('success')))->toBeTruthy();
        expect(is_array($handler = $res->getHandler('error')))->toBeTruthy();
    });

});
