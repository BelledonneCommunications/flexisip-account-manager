<?php

declare(strict_types=1);

namespace App\Providers;

use App\Account;
use Sabre\CardDAV;
use Sabre\DAV;
use Sabre\DAV\PropPatch;
use Sabre\CardDAV\Backend\AbstractBackend;
use Sabre\CardDAV\Plugin;

class ContactBackend extends AbstractBackend
{
    /**
     * Returns the list of addressbooks for a specific user.
     *
     * @param string $principalUri
     *
     * @return array
     */
    public function getAddressBooksForUser($principalUri)
    {
        $addressBooks = [[
            'id' => '1',
            'uri' => 'default',
            'principaluri' => 'principals/tester',
            '{DAV:}displayname' => 'Contacts',
            '{'.Plugin::NS_CARDDAV.'}addressbook-description' => 'User contacts',
            '{http://calendarserver.org/ns/}getctag' => '6697',
            '{http://sabredav.org/ns}sync-token' =>  '6697',
        ]];

        return $addressBooks;
    }

    /**
     * Updates properties for an address book.
     *
     * The list of mutations is stored in a Sabre\DAV\PropPatch object.
     * To do the actual updates, you must tell this object which properties
     * you're going to process with the handle() method.
     *
     * Calling the handle method is like telling the PropPatch object "I
     * promise I can handle updating this property".
     *
     * Read the PropPatch documentation for more info and examples.
     *
     * @param string $addressBookId
     */
    public function updateAddressBook($addressBookId, PropPatch $propPatch)
    {
        return false;
    }

    /**
     * Creates a new address book.
     *
     * @param string $principalUri
     * @param string $url          just the 'basename' of the url
     *
     * @return int Last insert id
     */
    public function createAddressBook($principalUri, $url, array $properties)
    {
        return 'default';
    }

    /**
     * Deletes an entire addressbook and all its contents.
     *
     * @param int $addressBookId
     */
    public function deleteAddressBook($addressBookId)
    {

    }

    /**
     * Returns all cards for a specific addressbook id.
     *
     * This method should return the following properties for each card:
     *   * carddata - raw vcard data
     *   * uri - Some unique url
     *   * lastmodified - A unix timestamp
     *
     * It's recommended to also return the following properties:
     *   * etag - A unique etag. This must change every time the card changes.
     *   * size - The size of the card in bytes.
     *
     * If these last two properties are provided, less time will be spent
     * calculating them. If they are specified, you can also ommit carddata.
     * This may speed up certain requests, especially with large cards.
     *
     * @param mixed $addressbookId
     *
     * @return array
     */
    public function getCards($addressbookId)
    {
        $result = [];
        $row['etag'] = '"etag"';
        $row['lastmodified'] = 1680010509;
        $row['uri'] = 'hop.vcf';
        $row['size'] = '147';
        $row['id'] = '2';
        $result[] = $row;


//        var_dump($result); exit;
        return $result;
    }

    /**
     * Returns a specific card.
     *
     * The same set of properties must be returned as with getCards. The only
     * exception is that 'carddata' is absolutely required.
     *
     * If the card does not exist, you must return false.
     *
     * @param mixed  $addressBookId
     * @param string $cardUri
     *
     * @return array
     */
    public function getCard($addressBookId, $cardUri)
    {
        $result['etag'] = '"123"';
        $result['lastmodified'] = 123;
        $result['uri'] = 'gnap';
        $result['carddata'] = Account::first()->toVcard4();

        return $result;
    }

    /**
     * Returns a list of cards.
     *
     * This method should work identical to getCard, but instead return all the
     * cards in the list as an array.
     *
     * If the backend supports this, it may allow for some speed-ups.
     *
     * @param mixed $addressBookId
     *
     * @return array
     */
    public function getMultipleCards($addressBookId, array $uris)
    {
        return $this->getCards($addressBookId);
    }

    /**
     * Creates a new card.
     *
     * The addressbook id will be passed as the first argument. This is the
     * same id as it is returned from the getAddressBooksForUser method.
     *
     * The cardUri is a base uri, and doesn't include the full path. The
     * cardData argument is the vcard body, and is passed as a string.
     *
     * It is possible to return an ETag from this method. This ETag is for the
     * newly created resource, and must be enclosed with double quotes (that
     * is, the string itself must contain the double quotes).
     *
     * You should only return the ETag if you store the carddata as-is. If a
     * subsequent GET request on the same card does not have the same body,
     * byte-by-byte and you did return an ETag here, clients tend to get
     * confused.
     *
     * If you don't return an ETag, you can just return null.
     *
     * @param mixed  $addressBookId
     * @param string $cardUri
     * @param string $cardData
     *
     * @return string|null
     */
    public function createCard($addressBookId, $cardUri, $cardData)
    {
        return null;
    }

    /**
     * Updates a card.
     *
     * The addressbook id will be passed as the first argument. This is the
     * same id as it is returned from the getAddressBooksForUser method.
     *
     * The cardUri is a base uri, and doesn't include the full path. The
     * cardData argument is the vcard body, and is passed as a string.
     *
     * It is possible to return an ETag from this method. This ETag should
     * match that of the updated resource, and must be enclosed with double
     * quotes (that is: the string itself must contain the actual quotes).
     *
     * You should only return the ETag if you store the carddata as-is. If a
     * subsequent GET request on the same card does not have the same body,
     * byte-by-byte and you did return an ETag here, clients tend to get
     * confused.
     *
     * If you don't return an ETag, you can just return null.
     *
     * @param mixed  $addressBookId
     * @param string $cardUri
     * @param string $cardData
     *
     * @return string|null
     */
    public function updateCard($addressBookId, $cardUri, $cardData)
    {
        return null;
    }

    /**
     * Deletes a card.
     *
     * @param mixed  $addressBookId
     * @param string $cardUri
     *
     * @return bool
     */
    public function deleteCard($addressBookId, $cardUri)
    {
        return false;
    }
}
