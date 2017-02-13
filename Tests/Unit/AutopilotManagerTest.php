<?php

namespace Tests\Unit;

use Autopilot\AutopilotContact;
use Autopilot\AutopilotManager;
use GuzzleHttp\Client;
use Phake;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class AutopilotManagerTest
 */
class AutopilotManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AutopilotManager
     */
    protected $manager;

    protected $stream;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->stream = Phake::mock(StreamInterface::CLASS);
        $response = Phake::mock(ResponseInterface::CLASS);
        Phake::when($response)->getBody()->thenReturn($this->stream);
        $client = Phake::mock(Client::CLASS);
        Phake::when($client)->post(Phake::anyParameters())->thenReturn($response);
        Phake::when($client)->get(Phake::anyParameters())->thenReturn($response);

        $this->manager = new AutopilotManager('foo', null, $client);
    }

    public function testGetContact()
    {
        Phake::when($this->stream)->getContents()->thenReturn(json_encode([
            'Email' => "foo.bar@baz.com",
            'created_at' => "2017-02-03T16:26:54.000Z",
            'first_visit_at' => "2017-01-12T10:08:10.761Z",
            'anywhere_page_visits' => [
                '//fr/sites/dashboard' => true,
                '//fr' => true,
            ],
            'updated_at' =>  "2017-02-08T15:33:40.000Z",
            'anywhere_utm' => [],
            'MailingCity' =>  "Paris",
            'MailingState' =>  "Ile-de-farnce",
            'MailingCountry' =>  "France",
            'Company' =>  "Baz industrie",
            'Title' =>  "Baz system",
            'LinkedIn' =>  "https://www.linkedin.com/in/foo-bar-609467",
            'NumberOfEmployees' =>  "10001",
            'employeesRange' => [
              'min' => 10001,
            ],
            'Industry' =>  "Information Technology and Services",
            'Name' =>  "Foo Bar",
            'company_priority' => false,
            'LastName' =>  "Bar",
            'FirstName' =>  "foo",
            'Salutation' =>  "Mr.",
            'lists' => [
                0 =>  "contactlist_C1A0F558-BB05-4C18-82C6-6347854F80A6",
                1 =>  "contactlist_8099FCD5-244B-44DC-9D6F-5A2F6F7ADBF8",
            ],
            'contact_id' =>  "person_0F17B343-EBA9-4721-AF44-96CE0EEDA103",
        ]));

        $contact = $this->manager->getContact('foo');

        $this->assertInstanceOf(AutopilotContact::CLASS, $contact);
        $this->assertSame('person_0F17B343-EBA9-4721-AF44-96CE0EEDA103', $contact->getFieldValue('contact_id'));
    }
}
