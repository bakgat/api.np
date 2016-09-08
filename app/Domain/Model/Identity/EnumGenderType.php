<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 25/06/16
 * Time: 23:30
 */

namespace App\Domain\Model\Identity;


use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;


class EnumGenderType extends Type
{

    const GENDER_MALE = 'M';
    const GENDER_FEMALE = 'F';
    const GENDER_OTHER = 'O';


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
        return sprintf("ENUM('M', 'F', 'O')", 'gender');
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value == null) {
            return null;
        }
        if(!static::validate($value)) {
            throw new \InvalidArgumentException('Invalid gender');
        }
        return new Gender($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!static::validate($value)) {
            throw new \InvalidArgumentException('Invalid gender');
        }
        return (string)$value;
    }

    /**
     * Gets the name of this type.
     *
     * @return string
     */
    public function getName()
    {
        return 'gender';
    }

    public static function validate($type)
    {
        return in_array($type, [
            self::GENDER_MALE,
            self::GENDER_FEMALE,
            self::GENDER_OTHER
        ]);
    }
}
