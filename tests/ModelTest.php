<?php
namespace app\spec\system;

use osoznan\patri\Model;


function testClass() {
    return new class() extends Model {
        public static function attributes() {
            return ['name', 'type', 'add'];
        }

        public function rules() {
            return [
                ['name', 'required'],
                ['name', 'str', 'min' => 3],
                ['type', 'required']
            ];
        }

        public static function labels() {
            return [
                'name' => 'A name',
                'type' => 'A type',
            ];
        }
    };
}

describe('Model', function() {
    beforeEach(function() {
    });

    it('getAttributes', function() {
        $testClass = testClass();
        $testClass->name = 'a name';
        $testClass->type = 'a type';
        $attrs = $testClass->getAttributes();

        expect(count($attrs) + 1)->toEqual(count($testClass::attributes()));
        expect($attrs['name'])->toEqual($testClass->name);
        expect($attrs['type'])->toEqual($testClass->type);
        expect(isset($attrs->id))->toBeEmpty();

    });

    it('load', function() {
        $testClass = testClass();
        $res = $testClass->load($attrs = ['name' => 'a123', 'falseProp' => '12']);

        expect($testClass->name)->toEqual($attrs['name']);
        expect(isset($attrs->id))->toBeEmpty();
        expect($testClass->id)->toEqual(null);

    });

    it('validate', function() {
        $testClass = testClass();

        // Wrong validation
        expect($testClass->validate())->toEqual(false);
        expect(count($testClass->getErrors()))->toEqual(2);

        // Wrong validation (type is wrong and just 1 name's rule is ok)
        $testClass->name = '1';
        expect($testClass->validate())->toEqual(false);
        expect(count($testClass->getErrors()))->toEqual(2);

        // validated just one rule ok
        $testClass->type = '1';
        expect($testClass->validate())->toEqual(false);
        expect(count($testClass->getErrors()))->toEqual(1);
    });

    it('getErrors', function() {
        $testClass = testClass();

        $testClass->validate();
        $errors = $testClass->getErrors();
        expect(count($errors))->toEqual(2);
        expect($errors['name'])->toBeTruthy();
        expect($errors['type'])->toBeTruthy();

        $testClass->name = 'a name';
        $testClass->validate();
        $errors = $testClass->getErrors();
        expect(count($errors))->toEqual(1);
        expect(isset($errors['name']))->toBeFalsy();
        expect($errors['type'])->toBeTruthy();

        $testClass->type = 'a type';
        $testClass->validate();
        $errors = $testClass->getErrors();
        expect(count($errors))->toEqual(0);
    });

    it('getFirstError', function() {
        $testClass = testClass();
        $testClass->validate();

        expect($testClass->getFirstError('name'))->toContain('значение');
    });

    it('getLabel', function() {
        $testClass = testClass();

        expect($testClass::getLabel('wrong label'))->toBeFalsy();
        expect($testClass::getLabel('name'))->toEqual('A name');
        expect($testClass::getLabel('type'))->toEqual('A type');
        expect($testClass::getLabel('add'))->toEqual('add');
    });

    it('getFirstErrors', function() {
        $testClass = testClass();
        $testClass->validate();

        $res = $testClass->getFirstErrors();

        expect(count($testClass->getErrors('name')))->toEqual(2);
        expect(count($res))->toEqual(2);
        expect(is_string($res['name']))->toBeTruthy();
    });

});
