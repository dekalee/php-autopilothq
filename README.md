# php-autopilothq

A php library for interacting with AutopilotHQ API http://docs.autopilot.apiary.io/#.

## Installation

```sh
$ composer require picr/php-autopilothq
```

## Usage
---
All interaction occurs in the `AutopilotManager` class.

### initialize manager
```php
$manager = new AutopilotManager($apiKey);
```
### checkContactExists
```php
$manager->checkContactExists($id|$email);
```

### getContact
```php
$manager->getContact($id|$email);
```

### saveContact
```php
$manager->saveContact(AutopilotContact $contact);
```

### saveContacts
```php
$manager->saveContacts(array $contacts);
```

### deleteContact
```php
$manager->deleteContact($id|$email);
```

### unsubscribeContact
```php
$manager->unsubscribeContact($id|$email);
```

### subscribeContact
```php
$manager->subscribeContact($id|$email);
```

### updateContactEmail
```php
$manager->updateContactEmail($oldEmail, $newEmail);
```

### getAllLists
```php
$manager->getAllLists();
```

### createList
```php
$manager->createList($list);
```

### getListByName
```php
$manager->getListByName($list);
```

### deleteList
```php
//TODO: AutopilotHQ hasn't implemented this yet
$manager->deleteList($list);
```

### getAllContactsInList
```php
$manager->getAllContactsInList($list);
```

### addContactToList
```php
$manager->addContactToList($list, $id|$email);
```

### removeContactFromList
```php
$manager->removeContactFromList($list, $id|$email);
```

### checkContactInList
```php
$manager->checkContactInList($list, $id|$email);
```

### allTriggers
```php
$manager->allTriggers();
```

### addContactToJourney
```php
$manager->addContactToJourney($journey, $id|$email);
```

### allRestHooks
```php
$manager->allRestHooks();
```

### deleteAllRestHooks
```php
$manager->deleteAllRestHooks();
```

### addRestHook
```php
$manager->addRestHook($event, $targetUrl);
```

### deleteRestHook
```php
$manager->deleteRestHook($hookId);
```

## AutopilotContact
---
### get value
```php
// magic method
$value = $contact->$name;
// getter
$value = $contact->getFieldValue($name);
```

### set value
```php
// magic method
$contact->$name = $value;
// setter
$contact->setFieldValue($name, $value);
```

### unset value
```php
// magic method
unset($contact->$name);
// method
$contact->unsetFieldValue($name);
```

### isset value
```php
// magic method
isset($contact->$name);
// method
$contact->issetFieldValue($name);
```

### getAllContactLists
```php
//NOTE: this only reads cached "lists" array returned for a "contact info" request
$contact->getAllContactLists();
```

### hasList
```php
//NOTE: this only reads cached "lists" array returned for a "contact info" request
$contact->hasList($list);
```

### fill
```php
// read array of values and populate properties
// NOTE: automatically formats attributes according to AutopilotHQ's "naming convention" (doesn't really exist)
$contact->fill([
    'firstName' => 'John',
    'lastName'  => 'Smith',
    'age'       => 42,
]);
```

### toRequest
```php
// return array ready to be pushed to the API
$user = $contact->toRequest();
```

### toArray
```php
// return array of [ property => value ] no matter if custom or defined field
$user = $contact->toArray();
/* [
 *    'firstName' => 'John',
 *    'lastName'  => 'Smith',
 *    'age'       => 42,
 * ]
 */
```

### jsonSerialize
```php
// json representation of "toArray()"
$user = $contact->jsonSerialze();
$user = json_encode($contact);
```

License
---

MIT
