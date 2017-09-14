<?php

namespace Test;

use AspectMock\Test as mock;
use Autopilot\AutopilotManager;
use Autopilot\AutopilotContact;
use Autopilot\AutopilotException;
use UnitTester;

class AutopilotCest
{
   /**
     * @var AutopilotManager
     */
    protected $pilot;

    /**
     * @var AutopilotContact
     */
    protected $contact;

    protected $fakeContact = 'person_9EAF39E4-9AEC-4134-964A-D9D8D54162E7';
    
    protected $fakeList = 'contactlist_9EAF39E4-9AEC-4134';

    /**
     * @var array
     */
    protected $exceptions;

    public function __construct()
    {
        $this->pilot = new AutopilotManager('key');

        $this->exceptions['contactNotFound'] = new AutopilotException('Contact could not be found.', 404);
        $this->exceptions['listNotFound'] = new AutopilotException('List does not exist.', 404);
    }

    public function _after(UnitTester $I)
    {
        mock::clean();
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    // CONTACT action methods
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

//    /**
//     * @param UnitTester $I
//     */
//    public function testSaveContact(UnitTester $I)
//    {
//        $I->wantTo('save a contact');
//
//        $contact = new AutopilotContact([
//            'FirstName' => 'John',
//            'LastName'  => 'Smith',
//            'Email'     => 'smith@picr.com',
//            'Phone'     => '(123)-456-7890',
//            'Age'       => 42,
//        ]);
//        $I->assertNull($contact->getFieldValue('contact_id'));
//
//        $contact = $this->pilot->saveContact($contact);
//        $I->assertNotNull($contact->getFieldValue('contact_id'));
//
//        $this->contact = $contact;
//    }
//
//    /**
//     * @before testSaveContact
//     *
//     * @param UnitTester $I
//     */
//    public function testGetContact(UnitTester $I)
//    {
//        $I->wantTo('get a contact details');
//
//        // test: get contact that doesn't exist
//        $I->seeException($this->exceptions['contactNotFound'], function() {
//            $this->pilot->getContact($this->fakeContact);
//        });
//
//        // test: get contact that exists
//        $contact = $this->pilot->getContact($this->contact->getFieldValue('contact_id'));
//        $I->assertNotNull($contact->getFieldValue('contact_id'));
//        $I->assertNotNull($contact->getFieldValue('created_at'));
//    }
//
//    /**
//     * @before testSaveContact
//     *
//     * @param UnitTester $I
//     */
//    public function testUnsubscribeContact(UnitTester $I)
//    {
//        $I->wantTo('unsubscribe a contact');
//
//        // test: unsubscribe contact that doesn't exist
//        $I->seeException($this->exceptions['contactNotFound'], function() {
//            $this->pilot->unsubscribeContact($this->fakeContact);
//        });
//
//        // test: unsubscribe contact that exists
//        $unsubscribed = $this->pilot->unsubscribeContact($this->contact->getFieldValue('contact_id'));
//        $I->assertTrue($unsubscribed);
//    }
//
//    /**
//     * @before testSaveContact
//     *
//     * @param UnitTester $I
//     */
//    public function testSubscribeContact(UnitTester $I)
//    {
//        $I->wantTo('subscribe a contact');
//
//        // test: subscribe contact that doesn't exist
//        $I->seeException($this->exceptions['contactNotFound'], function() {
//            $this->pilot->subscribeContact($this->fakeContact);
//        });
//
//        // test: subscribe contact that exists
//        $subscribed = $this->pilot->subscribeContact($this->contact->getFieldValue('contact_id'));
//        $I->assertTrue($subscribed);
//    }
//
//    /**
//     * @before testSaveContact
//     *
//     * @param UnitTester $I
//     */
//    public function testUpdateContactEmail(UnitTester $I)
//    {
//        $I->wantTo('update contact email');
//
//        // test: subscribe contact that doesn't exist
//        $I->seeException($this->exceptions['contactNotFound'], function() {
//            $this->pilot->updateContactEmail($this->fakeContact . '@autopilot.com', 'smith@email.com');
//        });
//
//        // test: subscribe contact that exists
//        $updated = $this->pilot->updateContactEmail($this->contact->getFieldValue('Email'), 'johnsmith@picr.com');
//        $I->assertTrue($updated);
//    }
//
//    /**
//     * @before testSaveContact
//     *
//     * @param UnitTester $I
//     */
//    public function testDeleteContact(UnitTester $I)
//    {
//        $I->wantTo('delete a contact');
//
//        // test: delete contact that doesn't exist
//        $I->seeException($this->exceptions['contactNotFound'], function() {
//            $this->pilot->deleteContact($this->fakeContact);
//        });
//
//        // test: delete contact that exists
//        $deleted = $this->pilot->deleteContact($this->contact->getFieldValue('contact_id'));
//        $I->assertTrue($deleted);
//    }
//
//    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
//    // LIST action methods
//    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
//
//    /**
//     * @param UnitTester $I
//     */
//    public function testGetAllLists(UnitTester $I)
//    {
//        $I->wantTo('get all lists of contacts');
//
//        // test: ensure at least one list exists
//        $lists = $this->pilot->getAllLists();
//        $I->assertGreaterThan(0, sizeof($lists));
//
//        // test: ensure all lists start with "contactlist_"
//        foreach($lists as $listId => $name) {
//            $I->assertTrue(strpos($listId, 'contactlist_') !== false);
//        }
//    }
//
//    /**
//     * @param UnitTester $I
//     */
//    public function testGetListByName(UnitTester $I)
//    {
//        $I->wantTo('get a list id by name');
//
//        $list = $this->pilot->getListByName('deleteThisList');
//        $I->assertNull($list);
//
//        // check before creating new to prevent pollution of lists
//        $list = $this->pilot->getListByName('testingList');
//        if ($list == null) {
//            $this->pilot->createList('testingList');
//        }
//
//        $listId = $this->pilot->getListByName('testingList');
//        $I->assertNotNull($listId);
//        $I->assertTrue(strpos($listId, 'contactlist_') !== false);
//    }
//
//    /**
//     * @param UnitTester $I
//     */
//    public function testCreateNewList(UnitTester $I)
//    {
//        $I->wantTo('create a new contact list');
//
//        // mock conditional expressions
//        $manager = mock::double(AutopilotManager::class, [
//            'apiPost' => null,
//        ]);
//
//        $listId = $this->pilot->createList('testingList');
//        $manager->verifyInvoked('apiPost');
////        $I->assertTrue(strpos($listId, 'contactlist_') !== false);
//    }
//
//    /**
//     * @before testSaveContact
//     * @before testGetListByName
//     *
//     * @param UnitTester $I
//     */
//    public function testAddContactToList(UnitTester $I)
//    {
//        $I->wantTo('add a contact to list');
//
//        // test: invalid list, valid contact
//        $I->seeException($this->exceptions['listNotFound'], function() {
//            $this->pilot->addContactToList($this->fakeList, $this->contact->getFieldValue('contact_id'));
//        });
//
//        // setup: get list id
//        $listId = $this->pilot->getListByName('testingList');
//        $I->assertNotNull($listId);
//
//        // test: valid list, invalid contact
//        $I->seeException($this->exceptions['contactNotFound'], function() use($listId) {
//            $this->pilot->addContactToList($listId, $this->fakeContact);
//        });
//
//        // test: valid list, valid contact
//        $added = $this->pilot->addContactToList($listId, $this->contact->getFieldValue('contact_id'));
//        $I->assertTrue($added);
//    }
//
//    /**
//     * @before testSaveContact
//     *
//     * @param UnitTester $I
//     */
//    public function testRemoveContactFromList(UnitTester $I)
//    {
//        $I->wantTo('remove a contact from a list');
//
//        // test: invalid list, valid contact
//        $I->seeException($this->exceptions['listNotFound'], function() {
//            $this->pilot->removeContactFromList($this->fakeList, $this->contact->getFieldValue('contact_id'));
//        });
//
//        // setup: get list id
//        $listId = $this->pilot->getListByName('testingList');
//        $I->assertNotNull($listId);
//
//        // test: valid list, invalid contact
//        $I->seeException($this->exceptions['contactNotFound'], function() use($listId) {
//            $this->pilot->removeContactFromList($listId, $this->fakeContact);
//        });
//
//        // test: valid list, valid contact
//        $added = $this->pilot->removeContactFromList($listId, $this->contact->getFieldValue('contact_id'));
//        $I->assertTrue($added);
//    }
//
//    /**
//     * @before testSaveContact
//     *
//     * @param UnitTester $I
//     */
//    public function testCheckContactInList(UnitTester $I)
//    {
//        $I->wantTo('check if contact is in a list');
//
//        // test: invalid list, valid contact
//        $I->seeException($this->exceptions['listNotFound'], function() {
//            $this->pilot->checkContactInList($this->fakeList, $this->contact->getFieldValue('contact_id'));
//        });
//
//        // setup: get list id
//        $listId = $this->pilot->getListByName('testingList');
//        $I->assertNotNull($listId);
//
//        // test: valid list, invalid contact
//        $I->seeException($this->exceptions['contactNotFound'], function() use($listId) {
//            $this->pilot->checkContactInList($listId, $this->fakeContact);
//        });
//
//        // test: valid list, valid contact
//        $isInList = $this->pilot->checkContactInList($listId, $this->contact->getFieldValue('contact_id'));
//        $I->assertFalse($isInList);
//
//        $this->pilot->addContactToList($listId, $this->contact->getFieldValue('contact_id'));
//        $isInList = $this->pilot->checkContactInList($listId, $this->contact->getFieldValue('contact_id'));
//        $I->assertTrue($isInList);
//
//        // cleanup
//        $this->pilot->removeContactFromList($listId, $this->contact->getFieldValue('contact_id'));
//        $isInList = $this->pilot->checkContactInList($listId, $this->contact->getFieldValue('contact_id'));
//        $I->assertFalse($isInList);
//    }
//
//    /**
//     * @before testSaveContact
//     *
//     * @param UnitTester $I
//     */
//    public function testGetAllContactsInList(UnitTester $I)
//    {
//        $I->wantTo('get all contacts in list');
//
////        // test: invalid list
//        $I->seeException($this->exceptions['listNotFound'], function() {
//            $this->pilot->getAllContactsInList($this->fakeList);
//        });
//
//        // setup: get list id
//        $listId = $this->pilot->getListByName('testingList');
//        $I->assertNotNull($listId);
//
//        // setup: add contact
//        $this->pilot->addContactToList($listId, $this->contact->getFieldValue('contact_id'));
//
//        $contacts = $this->pilot->getAllContactsInList($listId);
//        $I->assertEquals($contacts['total_contacts'], sizeof($contacts['contacts']));
//
//        /** @var AutopilotContact $contact */
//        foreach($contacts['contacts'] as $contact) {
//            $I->assertInstanceOf(AutopilotContact::class, $contact);
//            $I->assertNotNull($contact->getFieldValue('contact_id'));
//            $I->assertTrue($contact->hasList($listId));
//        }
//
//        // cleanup
//        $this->pilot->removeContactFromList($listId, $this->contact->getFieldValue('contact_id'));
//    }
}
