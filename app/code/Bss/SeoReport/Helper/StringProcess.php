<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_SeoReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoReport\Helper;

/**
 * Class StringProcess
 * @package Bss\SeoReport\Helper
 */
class StringProcess
{
    /**
     * @param string $text
     * @return string|string[]|null
     */
    public function removeHtmlTag($text) : string
    {
        $text = chop($text);
        $text = preg_replace("/\r\n|\r|\n/", ' ', $text);
        $text = preg_replace('/[\s]+/mu', ' ', $text);
        $text = strip_tags($text);
        $text = trim($text);
        return $text;
    }

    /**
     * @param string $string
     * @param string $tag
     * @return mixed|string
     */
    public function getTagValue($string, $tag)
    {
        $pattern = "/<{$tag}>(.*?)<\/{$tag}>/s";
        preg_match($pattern, $string, $matches);
        return isset($matches[1]) ? $matches[1] : '';
    }

    /**
     * @param string $string
     * @return string|string[]|null
     */
    public function removeSpecialString($string) : string
    {
        //Replaces all spaces with hyphens.
        $string = str_replace(' ', '-', $string);
        // Removes special chars.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);

        // Replaces multiple hyphens with single one.
        return preg_replace('/-+/', '-', $string);
    }

    /**
     * @param int $number
     * @return string
     */
    public function getRandomString($number = 6) : string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $number; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @param string $string
     * @param number $limit
     * @param string $stringAdd
     * @return string
     */
    public function truncateString($string, $limit, $stringAdd = '') : string
    {
        if ($string) {
            $resString = '';
            foreach (explode(" ", $string) as $word) {
                $resString .= $word . " ";
                if (strlen($resString) >= $limit) {
                    return $resString . $stringAdd;
                }
            }
            return $resString;
        }
        return '';
    }

}
