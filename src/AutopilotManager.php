<?php

namespace Autopilot;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class AutopilotManager
{
    /**
     * Autopilot ApiKey
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Client for interacting with api
     *
     * @var Client
     */
    protected $client;

    /**
     * Maximum contacts per request allowed by Autopilot
     *
     * @var int
     */
    protected static $MAX_UPLOADS = 100;

    /**
     * AutopilotManager constructor.
     *
     * @param string $apiKey
     *   Autopilot secret key.
     * @param string $apiHost
     *   Autopilot host URI.
     */
    public function __construct($apiKey, $apiHost = null, Client $client = null)
    {
        $this->apiKey = $apiKey;

        // instantiate client
        $this->client = (null !== $client)? $client: new Client([
            'base_uri' => $apiHost ?: 'https://api2.autopilothq.com/v1/',
        ]);
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    // CONTACT action methods
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    /**
     * @param $id
     *
     * @return AutopilotContact
     */
    public function getContact($id)
    {
        $response = $this->apiGet('contact/' . $id);

        $contact = new AutopilotContact($response);

        return $contact;
    }

    /**
     * @param AutopilotContact $contact
     *
     * @return AutopilotContact
     * @throws AutopilotException
     */
    public function saveContact(AutopilotContact $contact)
    {
        $response = $this->apiPost('contact', $contact->toRequest());

        if ($contact->getFieldValue('contact_id') !== null && $contact->getFieldValue('contact_id') !== $response['contact_id']) {
            throw AutopilotException::cannotOverwriteContactId();
        }

        $this->contactPostUpdate($contact, $response['contact_id']);

        return $contact;
    }

    /**
     * @param array $contacts
     * @param bool  $autosplit
     *
     * @return array
     * @throws AutopilotException
     */
    public function saveContacts(array $contacts, $autosplit = false)
    {
        if (! $autosplit && sizeof($contacts) > self::$MAX_UPLOADS) {
            throw AutopilotException::exceededContactsUploadLimit();
        }

        // list of contact ids corresponding to emails
        $contactIds = [];

        $request = [];
        foreach($contacts as $contact) {
            if (! $contact instanceof AutopilotContact) {
                throw AutopilotException::invalidContactType();
            }

            $request[] = $contact->toRequest($prependKey = false);

            // if using autosplit, whenever "request" array reaches MAX_UPLOADS,
            // submit the request, update contactIds->email reference, and reset "request" array
            if ($autosplit && sizeof($request) === self::$MAX_UPLOADS) {
                $response = $this->apiPost('contacts', ['contacts' => $request]);
                $contactIds = $response['email_contact_map'] + $contactIds;
                $request = [];
            }
        }

        $response = $this->apiPost('contacts', ['contacts' => $request]);
        $contactIds = $response['email_contact_map'] + $contactIds;

        // update all ids
        /** @var AutopilotContact $contact */
        foreach($contacts as $contact) {

            if (! isset($contactIds[$contact->getFieldValue('Email')])) {
                throw AutopilotException::contactsBulkSaveFailed('contact "' . $contact->getFieldValue('Email') . '" failed to upload');
            }

            $this->contactPostUpdate($contact, $contactIds[$contact->getFieldValue('Email')]);
        }

        return $response;
    }

    /**
     * @param $id
     *
     * @return boolean
     */
    public function deleteContact($id)
    {
        $this->apiDelete('contact/' . $id);

        return true;
    }

    /**
     * @param $id
     *
     * @return boolean
     */
    public function unsubscribeContact($id)
    {
        $this->apiPost('contact/' . $id . '/unsubscribe');

        return true;
    }

    /**
     * @param $id
     *
     * @return boolean
     */
    public function subscribeContact($id)
    {
        $contact = $this->getContact($id);
        $contact->setFieldValue('unsubscribed', false);
        $this->saveContact($contact);

        return true;
    }

    /**
     * @param $old
     * @param $new
     *
     * @return boolean
     */
    public function updateContactEmail($old, $new)
    {
        $contact = $this->getContact($old);
        $contact->setFieldValue('_NewEmail', $new);
        $this->saveContact($contact);

        return true;
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    // LIST action methods
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    /**
     * Get a list of all contact lists (ex: [ $listId => $listName ])
     *
     * @return array
     * @throws AutopilotException
     */
    public function getAllLists()
    {
        $response = $this->apiGet('lists');

        $lists = [];
        foreach($response['lists'] as $item) {
            $lists[$item['list_id']] = $item['title'];
        }

        return $lists;
    }

    /**
     * Create a new list
     * NOTE: Autopilot will create a new list if one with same name exists
     *
     * @param $name
     *
     * @return array
     * @throws AutopilotException
     */
    public function createList($name)
    {
        $response = $this->apiPost('list', [
            'name' => $name,
        ]);

        return $response['list_id'];
    }

    /**
     * Find a list by name
     *
     * @param $name
     *
     * @return null|string
     */
    public function getListByName($name)
    {
        $lists = $this->getAllLists();

        foreach($lists as $listId => $listName) {
            if ($listName === $name) {
                return $listId;
            }
        }

        return null;
    }

    /**
     * Attempt to delete list
     * NOTE: Autopilot currently doesn't support this feature
     *
     * @param $listId
     *
     * @return array
     * @throws AutopilotException
     */
    public function deleteList($listId)
    {
        throw new AutopilotException('delete is not implemented yet');

        $response = $this->apiPost('list/' . $listId, []);

        return $response;
    }

    /**
     * Get a list of all contacts in list
     *
     * @param      $listId
     * @param null $bookmark
     *
     * @return array|null
     * @throws AutopilotException
     */
    public function getAllContactsInList($listId, $bookmark = null)
    {
        $path = 'list/' . $listId . '/contacts';
        if (! is_null($bookmark)) {
            $path .= '/' . $bookmark;
        }

        try {

            $response = $this->apiGet($path);

        } catch (AutopilotException $e) {

            // for some reason Autopilot functionality isn't consistent here
            if ($e->getMessage() === '') {
                $e->setMessage('List does not exist.');
                $e->setReason('Not Found');
                throw $e;
            } else {
                throw $e;
            }
        }

        $list = [
            'total_contacts' => $response['total_contacts'],
            'contacts'       => [],
        ];

        if (isset($response['bookmark'])) {
            $list['bookmark'] = $response['bookmark'];
        }

        foreach($response['contacts'] as $data) {
            $contact = new AutopilotContact($data);
            $list['contacts'][] = $contact;
        }

        return $list;
    }

    /**
     * Add contact to list
     *
     * @param $listId
     * @param $contactId
     *
     * @return bool
     * @throws AutopilotException
     */
    public function addContactToList($listId, $contactId)
    {
        $response = $this->apiPost('list/' . $listId . '/contact/' . $contactId);

        // if contact is already a member
        if (isset($response['not_modified']) && $response['not_modified']) {
            return true;
        }

        return true;
    }

    /**
     * Remove contact from list
     *
     * @param $listId
     * @param $contactId
     *
     * @return bool
     * @throws AutopilotException
     */
    public function removeContactFromList($listId, $contactId)
    {
        $response = $this->apiDelete('list/' . $listId . '/contact/' . $contactId);

        // if contact wasn't a member in the first place
        if (isset($response['not_modified']) && $response['not_modified']) {
            return true;
        }

        return true;
    }

    /**
     * Check if contact is a member of list
     *
     * @param $listId
     * @param $contactId
     *
     * @return bool
     * @throws AutopilotException
     */
    public function checkContactInList($listId, $contactId)
    {
        try {

            $this->apiGet('list/' . $listId . '/contact/' . $contactId);

        } catch (AutopilotException $e) {

            // custom "Not Found" isn't an "error", but expected behavior
            if ($e->getMessage() !== '') {
                throw $e;
            }

            // "Not Found" message means "resource not found", not an "error"
            return false;
        }

        return true;
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    // Journey ACTION methods
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -


    public function allTriggers()
    {
        $response = $this->apiGet('triggers');

        return $response;
    }

    public function addContactToJourney($name, $contactId)
    {
        $response = $this->apiPost('trigger/' . $name . '/contact/' . $contactId);

        return $response;
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    // REST hooks methods
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    /**
     * Get all REST hooks
     *
     * @return array
     *
     * @throws AutopilotException
     */
    public function allRestHooks()
    {
        $response = $this->apiGet('hooks');

        return $response['hooks'];
    }

    /**
     * Deletes all REST hooks
     *
     * @return boolean
     *
     * @throws AutopilotException
     */
    public function deleteAllRestHooks()
    {
        $this->apiDelete('hooks');

        return true;
    }

    /**
     * Add REST hook
     * 
     * @param string $event
     * @param string $targetUrl
     *
     * @return string Returns hook ID
     *
     * @throws AutopilotException
     */
    public function addRestHook($event, $targetUrl)
    {
        $request = ['event' => $event, 'target_url' => $targetUrl];

        $response = $this->apiPost('hook', $request);

        return $response['hook_id'];
    }

    /**
     * Deletes a single REST hook
     *
     * @param string $hookId
     *
     * @return bool
     * 
     * @throws AutopilotException
     */
    public function deleteRestHook($hookId) 
    {
        $this->apiDelete('hook/' . $hookId);

        return true;
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    // REQUEST helpers
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    /**
     * POST helper
     *
     * @param       $path
     * @param array $data
     *
     * @return array
     * @throws AutopilotException
     */
    protected function apiPost($path, $data = [])
    {
        try {

            $options = ['headers' => $this->getApiHeaders()];
            if (sizeof($data) > 0) {
                $options['json'] = json_encode($data);
            }

            $response = $this->client->post($path, $options);

        } catch(ClientException $e) {
            throw AutopilotException::fromExisting($e);
        }

        $body = $response->getBody()->getContents();

        return json_decode($body, true);
    }

    /**
     * GET helper
     *
     * @param $path
     *
     * @return array|null
     * @throws AutopilotException
     */
    protected function apiGet($path)
    {
        try {

            $response = $this->client->get($path, [
                'headers' => $this->getApiHeaders(),
            ]);

        } catch(ClientException $e) {
            throw AutopilotException::fromExisting($e);
        }

        $body = $response->getBody()->getContents();

        return json_decode($body, true);
    }

    /**
     * DELETE helper
     *
     * @param $path
     *
     * @return array|null
     * @throws AutopilotException
     */
    protected function apiDelete($path)
    {
        try {

            $response = $this->client->delete($path, [
                'headers' => $this->getApiHeaders(),
            ]);

        } catch(ClientException $e) {
            throw AutopilotException::fromExisting($e);
        }

        $body = $response->getBody()->getContents();

        return json_decode($body, true);
    }

    /**
     * Get formatted headers
     *
     * @return array
     */
    protected function getApiHeaders()
    {
        return [
            'autopilotapikey' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    // Helpers
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    /**
     * Process a contact object after an update action
     *
     * @param AutopilotContact $contact
     * @param                  $contactId
     */
    protected function contactPostUpdate(AutopilotContact $contact, $contactId)
    {
        $contact->setFieldValue('contact_id', $contactId);

        // update email in case it was changed
        if ($contact->issetFieldValue('_NewEmail')) {
            $contact->setFieldValue('Email', $contact->getFieldValue('_NewEmail'));
            $contact->unsetFieldValue('_NewEmail');
        }
    }

}
