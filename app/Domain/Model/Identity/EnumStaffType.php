<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 28/06/16
 * Time: 14:35
 */

namespace App\Domain\Model\Identity;


use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class EnumStaffType extends Type
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
        foreach (StaffType::values() as $value) {
            $enums[] = (string)$value;
        }

        $enumString = implode('\', \'', $enums);
        return sprintf("ENUM('" . $enumString . "')", 'stafftype');
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value == null) {
            return null;
        }
        if (!in_array($value, StaffType::values())) {
            throw new \InvalidArgumentException('Invalid staff type');
        }
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, StaffType::values())) {
            throw new \InvalidArgumentException('Invalid staff type');
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
        return 'stafftype';
    }
}