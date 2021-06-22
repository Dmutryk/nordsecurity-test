<?php


namespace App\Database\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

/**
 * Encrypted datatype.
 */
class Encrypted extends Type
{
    const TYPE = 'encrypted';

    private $key;

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return $platform->getClobTypeDeclarationSQL($column);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === '') {
            return '';
        }

        if (!$value) {
            return null;
        }

        $key = $this->getKey();
        
        try {
            $ciphertext = Crypto::decrypt($value, $key);
        } catch (\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $exception) {
            //value was not encrypted
            return $value;
        }

        return $ciphertext;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === '') {
            return '';
        }

        if (!$value) {
            return null;
        }

        $key = $this->getKey();
        
        return Crypto::encrypt($value, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::TYPE;
    }

    public function setKey(Key $key) {
        $this->key = $key;
    }

    public function getKey() {
        return $this->key;
    }
}