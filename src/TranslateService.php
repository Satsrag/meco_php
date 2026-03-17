<?php

namespace Meco;

use Meco\Enums\CodeSeries;
use Meco\Enums\CodeType;
use Meco\Exception\MecoException;
use Meco\Rules\Delehi\DelehiTranslateRuleFrom;
use Meco\Rules\Delehi\DelehiTranslateRuleTo;
use Meco\Rules\Menk\MenkLetterTranslateRuleFrom;
use Meco\Rules\Menk\MenkLetterTranslateRuleTo;
use Meco\Rules\Menk\MenkShapeTranslateRuleFrom;
use Meco\Rules\Menk\MenkShapeTranslateRuleTo;
use Meco\Rules\Z52\Z52TranslateRuleFrom;
use Meco\Rules\Z52\Z52TranslateRuleTo;
use Meco\Translator\LetterFromTranslator;
use Meco\Translator\LetterToTranslator;
use Meco\Translator\ShapeTranslator;

class TranslateService
{
    /**
     * @param string $from CodeType constant
     * @param string $to   CodeType constant
     * @param string $text
     * @return string
     */
    public static function translate($from, $to, $text)
    {
        if ($text === '') {
            return '';
        }
        if ($from === $to) {
            return $text;
        }

        $fromSeries = CodeType::codeSeries($from);
        $toSeries = CodeType::codeSeries($to);

        if ($fromSeries === CodeSeries::LETTER && $toSeries === CodeSeries::SHAPE) {
            return self::translateLetterToShape($from, $to, $text);
        }
        if ($fromSeries === CodeSeries::SHAPE && $toSeries === CodeSeries::LETTER) {
            return self::translateShapeToLetter($from, $to, $text);
        }
        if ($fromSeries === CodeSeries::LETTER && $toSeries === CodeSeries::LETTER) {
            return self::translateLetterToLetter($from, $to, $text);
        }
        if ($fromSeries === CodeSeries::SHAPE && $toSeries === CodeSeries::SHAPE) {
            return self::translateShapeToShape($from, $to, $text);
        }

        throw new MecoException("Unsupported translation: {$from} to {$to}");
    }

    private static function translateLetterToShape($from, $to, $text)
    {
        $zvvnmod = self::translateFromLetter($from, $text);
        if ($to === CodeType::ZVVNMOD) {
            return $zvvnmod;
        }
        return self::translateToShape($to, $zvvnmod);
    }

    private static function translateShapeToLetter($from, $to, $text)
    {
        $zvvnmod = $text;
        if ($from !== CodeType::ZVVNMOD) {
            $zvvnmod = self::translateFromShape($from, $text);
        }
        return self::translateToLetter($to, $zvvnmod);
    }

    private static function translateLetterToLetter($from, $to, $text)
    {
        $zvvnmod = self::translateFromLetter($from, $text);
        return self::translateToLetter($to, $zvvnmod);
    }

    private static function translateShapeToShape($from, $to, $text)
    {
        if ($from === CodeType::ZVVNMOD) {
            return self::translateToShape($to, $text);
        }
        if ($to === CodeType::ZVVNMOD) {
            return self::translateFromShape($from, $text);
        }
        $zvvnmod = self::translateFromShape($from, $text);
        return self::translateToShape($to, $zvvnmod);
    }

    private static function translateFromLetter($from, $text)
    {
        switch ($from) {
            case CodeType::DELEHI:
                $rule = DelehiTranslateRuleFrom::getInstance();
                break;
            case CodeType::MENK_LETTER:
                $rule = MenkLetterTranslateRuleFrom::getInstance();
                break;
            case CodeType::OYUN:
                throw new MecoException('Oyun translation not implemented');
            default:
                throw new MecoException("Invalid letter encoding: {$from}");
        }
        $translator = new LetterFromTranslator($rule);
        return $translator->translate($text);
    }

    private static function translateToLetter($to, $text)
    {
        switch ($to) {
            case CodeType::DELEHI:
                $rule = DelehiTranslateRuleTo::getInstance();
                break;
            case CodeType::MENK_LETTER:
                $rule = MenkLetterTranslateRuleTo::getInstance();
                break;
            case CodeType::OYUN:
                throw new MecoException('Oyun reverse translation not implemented');
            default:
                throw new MecoException("Invalid letter encoding: {$to}");
        }
        $translator = new LetterToTranslator($rule);
        return $translator->translate($text);
    }

    private static function translateFromShape($from, $text)
    {
        switch ($from) {
            case CodeType::MENK_SHAPE:
                $rule = MenkShapeTranslateRuleFrom::getInstance();
                break;
            case CodeType::Z52:
                $rule = Z52TranslateRuleFrom::getInstance();
                break;
            default:
                throw new MecoException("Invalid shape encoding: {$from}");
        }
        $translator = new ShapeTranslator($rule);
        return $translator->translate($text);
    }

    private static function translateToShape($to, $text)
    {
        switch ($to) {
            case CodeType::MENK_SHAPE:
                $rule = MenkShapeTranslateRuleTo::getInstance();
                break;
            case CodeType::Z52:
                $rule = Z52TranslateRuleTo::getInstance();
                break;
            default:
                throw new MecoException("Invalid shape encoding: {$to}");
        }
        $translator = new ShapeTranslator($rule);
        return $translator->translate($text);
    }
}
