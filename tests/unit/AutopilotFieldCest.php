<?php

namespace Test\unit;

use Autopilot\AutopilotException;
use Autopilot\AutopilotField;
use UnitTester;

class AutopilotFieldCest
{
    protected $reservedFields = [
        'contact_id',
        'Email',
        'Twitter',
        'FirstName',
        'LastName',
        'Salutation',
        'Company',
        'NumberOfEmployees',
        'Title',
        'Industry',
        'Phone',
        'MobilePhone',
        'Fax',
        'Website',
        'MailingStreet',
        'MailingCity',
        'MailingState',
        'MailingPostalCode',
        'MailingCountry',
        'owner_name',
        'LeadSource',
        'Status',
        'LinkedIn',
        'unsubscribed',
        'custom',
        '_autopilot_session_id',
        '_autopilot_list',
    ];

    public function testDefaultFieldsWithReservedFields(UnitTester $I)
    {
        $I->wantTo('initialize a "string" Autopilot Field using a reserved field');

        // setup: all fields that can take a string and can be set
        $fields = [
            'Email',
            'Twitter',
            'FirstName',
            'LastName',
            'Salutation',
            'Company',
            'NumberOfEmployees',
            'Title',
            'Industry',
            'Phone',
            'MobilePhone',
            'Fax',
            'Website',
            'MailingStreet',
            'MailingCity',
            'MailingState',
            'MailingPostalCode',
            'MailingCountry',
            'LeadSource',
            'Status',
            'LinkedIn',
        ];

        // check reserved
        foreach($fields as $name) {
            $field = new AutopilotField($name, 'string value');
            $I->assertEquals($name, $field->getName());
            $I->assertEquals('string', $field->getType());
            $I->assertEquals('string value', $field->getValue());

            $I->assertTrue($field->isReserved());
            $I->assertFalse($field->isReadOnly());
        }
    }

    public function testCastFieldsWithReservedFields(UnitTester $I)
    {
        $I->wantTo('initialize a "cast" Autopilot Field using a reserved field');

        $field = new AutopilotField('unsubscribed', false);

        $I->assertEquals('unsubscribed', $field->getName());
        $I->assertEquals('boolean', $field->getType());
        $I->assertEquals(false, $field->getValue());

        $I->assertTrue($field->isReserved());
        $I->assertFalse($field->isReadOnly());

        $I->canSeeException(AutopilotException::typeMismatch('boolean', 'string'), function() {
            $field = new AutopilotField('unsubscribed', 'string value');
        });
    }

    public function testReadOnlyFieldsWithReservedFields(UnitTester $I)
    {
        $I->wantTo('initialize a "readonly" Autopilot Field using a reserved field');

        // setup: all fields that can take a string and can be set
        $fields = [
            'contact_id',
            'owner_name',
            'custom',
            '_autopilot_session_id',
            '_autopilot_list',
        ];

        // check reserved
        foreach($fields as $name) {
            $field = new AutopilotField($name, 'string value');
            $I->assertEquals($name, $field->getName());
            $I->assertEquals('string', $field->getType());
            $I->assertEquals('string value', $field->getValue());

            $I->assertTrue($field->isReserved());
            $I->assertTrue($field->isReadOnly());

            $I->assertNull($field->setValue('another value'));
            $I->assertEquals('string value', $field->getValue());
        }
    }

    public function testCustomFields(UnitTester $I)
    {
        $I->wantTo('save custom fields');

        // setup: fields with different types
        $fields = [
            'joined_at' => [
                'type'  => 'date',
                'value' => '2016-02-14T12:02:49',
                'name'  => 'JoinedAt',
            ],
            'age'      => [
                'type'  => 'integer',
                'value' => 42,
                'name'  => 'Age',
            ],
            'height'  => [
                'type'  => 'float',
                'value' => 6.2,
                'name'  => 'Height',
            ],
            'gender'         => [
                'type'  => 'string',
                'value' => 'male',
                'name'  => 'Gender',
            ],
            'is licensed'  => [
                'type'  => 'boolean',
                'value' => true,
                'name'  => 'Is Licensed',
            ],
        ];

        // test: ensure type is preserved when passing into constructor
        foreach($fields as $name => $value) {
            $field = new AutopilotField($name, $value['value'], $value['type']);

            $I->assertEquals($value['name'], $field->getName());
            $I->assertEquals($value['type'], $field->getType());
            $I->assertEquals($value['value'], $field->getValue());

            $I->assertFalse($field->isReserved());
            $I->assertFalse($field->isReadOnly());

            $customs[] = $field;
        }

        // test: ensure type is preserved using "auto" calculation
        $customs = [];
        foreach($fields as $name => $value) {
            $field = new AutopilotField($name, $value['value']);

            $I->assertEquals($value['name'], $field->getName());
            $I->assertEquals($value['type'], $field->getType());
            $I->assertEquals($value['value'], $field->getValue());

            $I->assertFalse($field->isReserved());
            $I->assertFalse($field->isReadOnly());

            $customs[] = $field;
        }

        // setup: set invalid types (in order to force invalid values
        $values = [6.2, true, '6.2', '2016-02-14T12:02:49', 42,];

        // test: ensure excetions are thrown for invalid types
        /** @var AutopilotField $custom */
        foreach($customs as $ix => $custom) {
            $checkValue = $values[$ix];
            $expectedType = $this->getFieldTypeByValue($checkValue);

            $I->canSeeException(AutopilotException::typeMismatch($custom->getType(), $expectedType), function() use($custom, $checkValue) {
                $custom->setValue($checkValue);
            });

        }
    }

    public function testForceMatchReservedFields(UnitTester $I)
    {
        $I->wantTo('match custom fields as "reserved" fields');

        // setup: custom fields that should be matched as reserved fields
        $fields = [
            'mobile'     => 'MobilePhone',
            'site'       => 'Website',
            'webpage'    => 'Website',
            'webPage'    => 'Website',
            'street'     => 'MailingStreet',
            'city'       => 'MailingCity',
            'state'      => 'MailingState',
            'postalCode' => 'MailingPostalCode',
            'country'    => 'MailingCountry',
            'zip'        => 'MailingPostalCode',
        ];

        // test: check if name is parsed correctly (don't care about value)
        foreach($fields as $name => $value) {
            $field = new AutopilotField($name, '');

            $I->assertEquals($value, $field->getName());

            $I->assertTrue($field->isReserved());
            $I->assertFalse($field->isReadOnly());
        }
    }

    public function testParseAutopilotCustomField(UnitTester $I)
    {
        $I->wantTo('parse Autopilot custom fields from response');

        // setup: fields with different types
        $fields = [
            'date--JoinedAt' => [
                'type'  => 'date',
                'value' => '2016-02-14T12:02:49',
                'name'  => 'JoinedAt',
            ],
            'integer--Age'      => [
                'type'  => 'integer',
                'value' => 42,
                'name'  => 'Age',
            ],
            'float--Height'  => [
                'type'  => 'float',
                'value' => 6.2,
                'name'  => 'Height',
            ],
            'string--Gender'         => [
                'type'  => 'string',
                'value' => 'male',
                'name'  => 'Gender',
            ],
            'boolean--Is--Licensed'  => [
                'type'  => 'boolean',
                'value' => true,
                'name'  => 'Is Licensed',
            ],
        ];

        // test: ensure types are preserved when passing type into constructor
        foreach($fields as $name => $value) {

            $field = new AutopilotField($name, $value['value'], $value['type']);
            $I->assertEquals($value['name'], $field->getName());
            $I->assertEquals($value['type'], $field->getType());
            $I->assertEquals($value['value'], $field->getValue());

            $I->assertFalse($field->isReserved());
            $I->assertFalse($field->isReadOnly());
        }

        // test: ensure types are auto-calculated properly
        foreach($fields as $name => $value) {

            $field = new AutopilotField($name, $value['value']);
            $I->assertEquals($value['name'], $field->getName());
            $I->assertEquals($value['type'], $field->getType());
            $I->assertEquals($value['value'], $field->getValue());

            $I->assertFalse($field->isReserved());
            $I->assertFalse($field->isReadOnly());
        }
    }

    public function testFieldNameFormatting(UnitTester $I)
    {
        $I->wantTo('get the formatted name from the field');

        $fields = [
            'joined_at' => [
                'type'  => 'date',
                'value' => '2016-02-14T12:02:49',
                'name'  => 'date--JoinedAt',
            ],
            'age'      => [
                'type'  => 'integer',
                'value' => 42,
                'name'  => 'integer--Age',
            ],
            'height'  => [
                'type'  => 'float',
                'value' => 6.2,
                'name'  => 'float--Height',
            ],
            'gender'         => [
                'type'  => 'string',
                'value' => 'male',
                'name'  => 'string--Gender',
            ],
            'is licensed'  => [
                'type'  => 'boolean',
                'value' => true,
                'name'  => 'boolean--Is--Licensed',
            ],
            'MailingStreet'  => [
                'type'  => 'string',
                'value' => '123 NE 45th St',
                'name'  => 'MailingStreet',
            ],
            'MailingCity'  => [
                'type'  => 'string',
                'value' => 'Portland',
                'name'  => 'MailingCity',
            ],
            'MailingState'  => [
                'type'  => 'string',
                'value' => 'OR',
                'name'  => 'MailingState',
            ],
            'MailingPostalCode'  => [
                'type'  => 'string',
                'value' => 98662,
                'name'  => 'MailingPostalCode',
            ],
            'MailingCountry'  => [
                'type'  => 'string',
                'value' => 'USA',
                'name'  => 'MailingCountry',
            ],
        ];

        foreach($fields as $name => $data) {
            $field = new AutopilotField($name, $data['value']);

            $I->assertEquals($data['name'], $field->formatName());
        }
    }

    protected function getFieldTypeByValue($value)
    {
        $type = gettype($value);
        if ($type === 'double') {
            return 'float';
        }

        // datetime string
        $matches = [];
        $pattern = '/(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2})(\s|\+\d{4}|\+\d{2}:\d{2}|Z)?/';
        preg_match($pattern, $value, $matches);
        if (sizeof($matches) > 0) {
            return 'date';
        }

        return $type;
    }
}