<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 17/07/16
 * Time: 21:08
 */

namespace App\Domain\Model\Evaluation;


use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class EnumEvaluationType extends Type
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
        foreach (EvaluationType::values() as $value) {
            $enums[] = (string)$value;
        }

        $enumString = implode('\', \'', $enums);
        return sprintf("ENUM('" . $enumString . "')", 'evaluationtype');
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, EvaluationType::values())) {
            throw new \InvalidArgumentException('Invalid evaluation type');
        }
        return new EvaluationType($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, EvaluationType::values())) {
            throw new \InvalidArgumentException('INvalid evaluation type');
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
        return 'evaluationtype';
    }
}