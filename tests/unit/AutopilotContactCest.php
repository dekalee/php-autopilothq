<?php

namespace Test;

use Autopilot\AutopilotContact;
use Autopilot\AutopilotException;
use UnitTester;

class AutopilotContactCest
{
    public function testPopulateContact(UnitTester $I)
    {
        $I->wantTo('populate Autopilot contact object');

        $values = [
            'FirstName'        => 'John',
            'LastName'         => 'Smith',
            'Age'              => 42,
            'Birthday'         => '1970-03-29T02:03:13',
            'Category'         => 'DEFAULT',
            'Completed Status' => 75.2,
        ];

        // test: empty constructor, use fill
        $contact = new AutopilotContact();
        $contact->fill($values);
        $this->checkContactFieldValues($I, $values, $contact);

        // test: pass values into constructor
        $contact = new AutopilotContact($values);
        $this->checkContactFieldValues($I, $values, $contact);

        // test: empty constructor, set individual values
        $contact = new AutopilotContact();
        foreach($values as $name => $value) {
            $contact->setFieldValue($name, $value);
        }
        $this->checkContactFieldValues($I, $values, $contact);

        // setup: pass appropriate values to override
        $newValues = [
            'FirstName'        => 'Jack',
            'LastName'         => 'Douglas',
            'Age'              => 20,
            'Birthday'         => '1995-03-29T02:03:13',
            'Category'         => 'NEW',
            'Completed Status' => 00.00,
        ];

        // test: overwrite existing properties
        $contact = new AutopilotContact($values);
        foreach($newValues as $name => $value) {
            $contact->setFieldValue($name, $value);
        }

        // setup: pass invalid values to override
        $newValues = [
            'FirstName'        => [
                'exType' => 'string',
                'reType' => 'boolean',
                'value'  => true,
            ],
            'LastName'         => [
                'exType' => 'string',
                'reType' => 'float',
                'value'  => 0.00,
            ],
            'age'              => [
                'exType' => 'integer',
                'reType' => 'string',
                'value'  => '20',
            ],
            'Birthday'         => [
                'exType' => 'date',
                'reType' => 'string',
                'value'  => '1995-03-29 02:03:93',    // invalid date format
            ],
            'category'         => [
                'exType' => 'string',
                'reType' => 'integer',
                'value'  => 1,
            ],
            'Completed Status' => [
                'exType' => 'float',
                'reType' => 'integer',
                'value'  => 5,
            ],
        ];

        $contact = new AutopilotContact($values);
        foreach($newValues as $name => $data) {
            $value = $data['value'];
            $I->canSeeException(AutopilotException::typeMismatch($data['exType'], $data['reType']), function() use($contact, $name, $value) {
                $contact->setFieldValue($name, $value);
            });
        }
    }

    public function testContactToArray(UnitTester $I)
    {
        $I->wantTo('get array value of contact');

        $values = [
            'FirstName'        => 'John',
            'LastName'         => 'Smith',
            'Age'              => 42,
            'Birthday'         => '1970-03-29T02:03:13',
            'Category'         => 'DEFAULT',
            'Completed Status' => 75.2,
        ];

        $contact = new AutopilotContact($values);
        $I->assertEquals($values, $contact->toArray());
    }

    public function testContactToRequest(UnitTester $I)
    {
        $values = [
            'FirstName'               => 'John',
            'LastName'                => 'Smith',
            'custom_fields' => [
                [
                    'kind'      => 'Age',
                    'value'     => 42,
                    'fieldType' => 'integer',
                ],
                [
                    'kind'      => 'Birthday',
                    'value'     => '1970-03-29T02:03:13',
                    'fieldType' => 'date',
                ],
                [
                    'kind'      => 'Category',
                    'value'     => 'DEFAULT',
                    'fieldType' => 'string',
                ],
                [
                    'kind'      => 'Completed Status',
                    'value'     => 75.2,
                    'fieldType' => 'float',
                ],
            ],
        ];

        $contact = new AutopilotContact($values);

        $expected = [
            'contact' => [
                'FirstName' => 'John',
                'LastName'  => 'Smith',
                'custom'    => [
                    'integer--Age'             => 42,
                    'date--Birthday'           => '1970-03-29T02:03:13',
                    'string--Category'         => 'DEFAULT',
                    'float--Completed--Status' => 75.2,
                ],
            ],
        ];

        // test: full object
        $I->assertEquals($expected, $contact->toRequest());
        // test: without "contact" key
        $I->assertEquals($expected['contact'], $contact->toRequest($prependKey = false));
    }

    public function testContactFieldsCanBeCleared(UnitTester $I)
    {
        $I->wantTo('clear contact fields');

        $values = [
            'FirstName'               => null,
            'LastName'                => null,
            'custom_fields' => [
                [
                    'kind'      => 'Age',
                    'value'     => null,
                    'fieldType' => 'integer',
                ],
                [
                    'kind'      => 'Birthday',
                    'value'     => null,
                    'fieldType' => 'date',
                ],
                [
                    'kind'      => 'Category',
                    'value'     => null,
                    'fieldType' => 'string',
                ],
                [
                    'kind'      => 'Completed Status',
                    'value'     => null,
                    'fieldType' => 'float',
                ],
            ],
        ];

        $contact = new AutopilotContact($values);

        $expected = [
            'contact' => [
                'FirstName' => null,
                'LastName'  => null,
                'custom'    => [
                    'integer--Age'             => null,
                    'date--Birthday'           => null,
                    'string--Category'         => null,
                    'float--Completed--Status' => null,
                ],
            ],
        ];
        
        $I->assertEquals($expected, $contact->toRequest());
    }

    public function testContactMagicGettersAndSetters(UnitTester $I)
    {
        $I->wantTo('set and get values using magic methods');

        // setup: pass appropriate values to override
        $values = [
            'FirstName'        => 'Jack',
            'LastName'         => 'Douglas',
            'Age'              => 20,
            'Birthday'         => '1995-03-29T02:03:93',
            'Category'         => 'NEW',
            'Completed Status' => 00.00,
        ];

        // test: overwrite existing properties
        $contact = new AutopilotContact();
        foreach($values as $name => $value) {
            $contact->$name = $value;
            $I->assertEquals($value, $contact->$name);
        }

    }

    protected function checkContactFieldValues(UnitTester $I, array $values, AutopilotContact $contact)
    {
        foreach($values as $name => $value) {
            $I->assertEquals($value, $contact->getFieldValue($name));
        }
    }
}