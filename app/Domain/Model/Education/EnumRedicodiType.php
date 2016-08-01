<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 1/08/16
 * Time: 21:07
 */

namespace App\Domain\Model\Education;


use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class EnumRedicodiType extends Type
{
    /**
     * Gets the SQL declaration snippet for a field of this type.
     *
     * @param array $fieldDeclaration The field declaration.
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform The currently used database platform.
     *
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $enums = [];
        foreach (Redicodi::values() as $value) {
            $enums[] = (string)$value;
        }

        $enumString = implode('\', \'', $enums);
        return sprintf("ENUM('" . $enumString . "')", 'redicoditype');
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, Redicodi::values())) {
            throw new \InvalidArgumentException('Invalid Redicodi type');
        }
        return new Redicodi($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, Redicodi::values())) {
            throw new \InvalidArgumentException('Invalid Redicodi type');
        }
        return (string)$value;
    }

    /**
     * Gets the name of this type.
     *
     * @return string
     *
     * @todo Needed?
     */
    public function getName()
    {
        return 'redicoditype';
    }
}