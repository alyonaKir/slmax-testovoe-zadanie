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
 * @package    Bss_HrefLang
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HrefLang\Block\System\Form\Field;

/**
 * Class Country
 *
 * @package Bss\HrefLang\Block\System\Form\Field
 */
class Country extends \Magento\Framework\View\Element\Html\Select
{

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        $countries = $this->getCountries();
        if (!$this->getOptions()) {
            foreach ($countries as $code => $country) {
                $this->addOption($code, $country);
            }
        }
        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(ExcessiveMethodLength)
     */
    public function getCountries()
    {
        $country['x-default'] = 'Global';
        $country['ab'] = 'Abkhaz (ab)';
        $country['aa'] = 'Afar (aa)';
        $country['af'] = 'Afrikaans (af)';
        $country['ak'] = 'Akan (ak)';
        $country['sq'] = 'Albanian (sq)';
        $country['am'] = 'Amharic (am)';
        $country['ar'] = 'Arabic (ar)';
        $country['an'] = 'Aragonese (an)';
        $country['hy'] = 'Armenian (hy)';
        $country['as'] = 'Assamese (as)';
        $country['av'] = 'Avaric (av)';
        $country['ae'] = 'Avestan (ae)';
        $country['ay'] = 'Aymara (ay)';
        $country['az'] = 'Azerbaijani (az)';
        $country['bm'] = 'Bambara (bm)';
        $country['ba'] = 'Bashkir (ba)';
        $country['eu'] = 'Basque (eu)';
        $country['be'] = 'Belarusian (be)';
        $country['bn'] = 'Bengali, Bangla (bn)';
        $country['bh'] = 'Bihari (bh)';
        $country['bi'] = 'Bislama (bi)';
        $country['bs'] = 'Bosnian (bs)';
        $country['br'] = 'Breton (br)';
        $country['bg'] = 'Bulgarian (bg)';
        $country['my'] = 'Burmese (my)';
        $country['ca'] = 'Catalan (ca)';
        $country['ch'] = 'Chamorro (ch)';
        $country['ce'] = 'Chechen (ce)';
        $country['ny'] = 'Chichewa, Chewa, Nyanja (ny)';
        $country['zh'] = 'Chinese (zh)';
        $country['cv'] = 'Chuvash (cv)';
        $country['kw'] = 'Cornish (kw)';
        $country['co'] = 'Corsican (co)';
        $country['cr'] = 'Cree (cr)';
        $country['hr'] = 'Croatian (hr)';
        $country['cs'] = 'Czech (cs)';
        $country['da'] = 'Danish (da)';
        $country['dv'] = 'Divehi, Dhivehi, Maldivian (dv)';
        $country['nl'] = 'Dutch (nl)';
        $country['dz'] = 'Dzongkha (dz)';
        $country['en'] = 'English (en)';
        $country['eo'] = 'Esperanto (eo)';
        $country['et'] = 'Estonian (et)';
        $country['ee'] = 'Ewe (ee)';
        $country['fo'] = 'Faroese (fo)';
        $country['fj'] = 'Fijian (fj)';
        $country['fi'] = 'Finnish (fi)';
        $country['fr'] = 'French (fr)';
        $country['ff'] = 'Fula, Fulah, Pulaar, Pular (ff)';
        $country['gl'] = 'Galician (gl)';
        $country['ka'] = 'Georgian (ka)';
        $country['de'] = 'German (de)';
        $country['el'] = 'Greek (modern) (el)';
        $country['gn'] = 'Guaraní (gn)';
        $country['gu'] = 'Gujarati (gu)';
        $country['ht'] = 'Haitian, Haitian Creole (ht)';
        $country['ha'] = 'Hausa (ha)';
        $country['he'] = 'Hebrew  (he)';
        $country['hz'] = 'Herero (hz)';
        $country['hi'] = 'Hindi (hi)';
        $country['ho'] = 'Hiri Motu (ho)';
        $country['hu'] = 'Hungarian (hu)';
        $country['ia'] = 'Interlingua (ia)';
        $country['id'] = 'Indonesian (id)';
        $country['ie'] = 'Interlingue (ie)';
        $country['ga'] = 'Irish (ga)';
        $country['ig'] = 'Igbo (ig)';
        $country['ik'] = 'Inupiaq (ik)';
        $country['io'] = 'Ido (io)';
        $country['is'] = 'Icelandic (is)';
        $country['it'] = 'Italian (it)';
        $country['iu'] = 'Inuktitut (iu)';
        $country['ja'] = 'Japanese (ja)';
        $country['jv'] = 'Javanese (jv)';
        $country['kl'] = 'Kalaallisut, Greenlandic (kl)';
        $country['kn'] = 'Kannada (kn)';
        $country['kr'] = 'Kanuri (kr)';
        $country['ks'] = 'Kashmiri (ks)';
        $country['kk'] = 'Kazakh (kk)';
        $country['km'] = 'Khmer (km)';
        $country['ki'] = 'Kikuyu, Gikuyu (ki)';
        $country['rw'] = 'Kinyarwanda (rw)';
        $country['ky'] = 'Kyrgyz (ky)';
        $country['kv'] = 'Komi (kv)';
        $country['kg'] = 'Kongo (kg)';
        $country['ko'] = 'Korean (ko)';
        $country['ku'] = 'Kurdish (ku)';
        $country['kj'] = 'Kwanyama, Kuanyama (kj)';
        $country['la'] = 'Latin (la)';
        $country['lb'] = 'Luxembourgish, Letzeburgesch (lb)';
        $country['lg'] = 'Ganda (lg)';
        $country['li'] = 'Limburgish, Limburgan, Limburger (li)';
        $country['ln'] = 'Lingala (ln)';
        $country['lo'] = 'Lao (lo)';
        $country['lt'] = 'Lithuanian (lt)';
        $country['lu'] = 'Luba-Katanga (lu)';
        $country['lv'] = 'Latvian (lv)';
        $country['gv'] = 'Manx (gv)';
        $country['mk'] = 'Macedonian (mk)';
        $country['mg'] = 'Malagasy (mg)';
        $country['ms'] = 'Malay (ms)';
        $country['ml'] = 'Malayalam (ml)';
        $country['mt'] = 'Maltese (mt)';
        $country['mi'] = 'Māori (mi)';
        $country['mr'] = 'Marathi  (mr)';
        $country['mh'] = 'Marshallese (mh)';
        $country['mn'] = 'Mongolian (mn)';
        $country['na'] = 'Nauru (na)';
        $country['nv'] = 'Navajo, Navaho (nv)';
        $country['nd'] = 'Northern Ndebele (nd)';
        $country['ne'] = 'Nepali (ne)';
        $country['ng'] = 'Ndonga (ng)';
        $country['nb'] = 'Norwegian Bokmål (nb)';
        $country['nn'] = 'Norwegian Nynorsk (nn)';
        $country['no'] = 'Norwegian (no)';
        $country['ii'] = 'Nuosu (ii)';
        $country['nr'] = 'Southern Ndebele (nr)';
        $country['oc'] = 'Occitan (oc)';
        $country['oj'] = 'Ojibwe, Ojibwa (oj)';
        $country['cu'] = 'Old Church Slavonic, Old Bulgarian (cu)';
        $country['om'] = 'Oromo (om)';
        $country['or'] = 'Oriya (or)';
        $country['os'] = 'Ossetian, Ossetic (os)';
        $country['pa'] = 'Panjabi, Punjabi (pa)';
        $country['pi'] = 'Pāli (pi)';
        $country['fa'] = 'Persian (Farsi) (fa)';
        $country['pl'] = 'Polish (pl)';
        $country['ps'] = 'Pashto, Pushto (ps)';
        $country['pt'] = 'Portuguese (pt)';
        $country['qu'] = 'Quechua (qu)';
        $country['rm'] = 'Romansh (rm)';
        $country['rn'] = 'Kirundi (rn)';
        $country['ro'] = 'Romanian (ro)';
        $country['ru'] = 'Russian (ru)';
        $country['sa'] = 'Sanskrit  (sa)';
        $country['sc'] = 'Sardinian (sc)';
        $country['sd'] = 'Sindhi (sd)';
        $country['se'] = 'Northern Sami (se)';
        $country['sm'] = 'Samoan (sm)';
        $country['sg'] = 'Sango (sg)';
        $country['sk'] = 'Saraiki,Seraiki,Siraiki (sk)';
        $country['sr'] = 'Serbian (sr)';
        $country['gd'] = 'Scottish Gaelic, Gaelic (gd)';
        $country['sn'] = 'Shona (sn)';
        $country['si'] = 'Sinhala (si)';
        $country['sk'] = 'Slovak (sk)';
        $country['sl'] = 'Slovene (sl)';
        $country['so'] = 'Somali (so)';
        $country['st'] = 'Southern Sotho (st)';
        $country['es'] = 'Spanish (es)';
        $country['su'] = 'Sundanese (su)';
        $country['sw'] = 'Swahili (sw)';
        $country['ss'] = 'Swati (ss)';
        $country['sv'] = 'Swedish (sv)';
        $country['ta'] = 'Tamil (ta)';
        $country['te'] = 'Telugu (te)';
        $country['tg'] = 'Tajik (tg)';
        $country['th'] = 'Thai (th)';
        $country['ti'] = 'Tigrinya (ti)';
        $country['bo'] = 'Tibetan (bo)';
        $country['tk'] = 'Turkmen (tk)';
        $country['tl'] = 'Tagalog (tl)';
        $country['tn'] = 'Tswana (tn)';
        $country['to'] = 'Tonga  (to)';
        $country['tr'] = 'Turkish (tr)';
        $country['ts'] = 'Tsonga (ts)';
        $country['tt'] = 'Tatar (tt)';
        $country['tw'] = 'Twi (tw)';
        $country['ty'] = 'Tahitian (ty)';
        $country['ug'] = 'Uyghur (ug)';
        $country['uk'] = 'Ukrainian (uk)';
        $country['ur'] = 'Urdu (ur)';
        $country['uz'] = 'Uzbek (uz)';
        $country['ve'] = 'Venda (ve)';
        $country['vi'] = 'Vietnamese (vi)';
        $country['vo'] = 'Volapük (vo)';
        $country['wa'] = 'Walloon (wa)';
        $country['cy'] = 'Welsh (cy)';
        $country['wo'] = 'Wolof (wo)';
        $country['fy'] = 'Western Frisian (fy)';
        $country['xh'] = 'Xhosa (xh)';
        $country['yi'] = 'Yiddish (yi)';
        $country['yo'] = 'Yoruba (yo)';
        $country['za'] = 'Zhuang, Chuang (za)';
        $country['zu'] = 'Zulu (zu)';
        return $country;
    }
}
