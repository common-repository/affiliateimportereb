<?php
if (!class_exists('EBDN_PriceFormula')) {
    /**
     * Class EBDN_PriceFormula
     */
    class EBDN_PriceFormula
    {
        public $id = 0;
        public $type = "";
        public $pos = 0;
        public $category = 0;
        public $category_name = "";
        public $min_price = 0;
        public $max_price = 0;
        public $sign = "=";
        public $value = 1;
        public $discount1 = "";
        public $discount2 = "";
        public $type_name;

        public function __construct($id = 0)
        {
            $this->id = $id;
            if ($this->id) {
                $formula = EBDN_PriceFormula::load($id);
                if ($formula) {
                    foreach ($formula as $field => $val) {
                        $this->$field = $val;
                    }
                }
            }
        }

        public static function load($id)
        {
            /** @var wpdb $wpdb */
            global $wpdb;

            $formula = false;

            $results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . EBDN_TABLE_PRICE_FORMULA . " WHERE id='$id'");

            if ($results) {
                $formula = new EBDN_PriceFormula();
                $formula->id = $results[0]->id;
                $formula->pos = $results[0]->pos;

                $f_data = unserialize($results[0]->formula);
                foreach ($f_data as $field => $value) {
                    if (property_exists(get_class($formula), $field)) {
                        $formula->$field = $value;
                    }
                }
            }
            return $formula;
        }

        /**
         * @param EBDN_PriceFormula $formula
         * @return mixed
         */
        public static function save(&$formula)
        {
            /** @var wpdb $wpdb */
            global $wpdb;

            $f_data = array("type" => $formula->type,
                "category" => $formula->category,
                "category_name" => $formula->category_name,
                "min_price" => $formula->min_price,
                "max_price" => $formula->max_price,
                "sign" => $formula->sign,
                "value" => $formula->value,
                "discount1" => $formula->discount1,
                "discount2" => $formula->discount2,);

            if ($formula->id) {
                $wpdb->update($wpdb->prefix . EBDN_TABLE_PRICE_FORMULA, array("pos" => $formula->pos, "formula" => serialize($f_data)), array('id' => $formula->id));
            } else {
                $wpdb->insert($wpdb->prefix . EBDN_TABLE_PRICE_FORMULA, array("pos" => $formula->pos, "formula" => serialize($f_data)));
                $formula->id = $wpdb->insert_id;
            }

            return $formula;
        }

        /**
         * @param $id
         */
        public static function delete($id)
        {
            /** @var wpdb $wpdb */
            global $wpdb;

            $wpdb->delete($wpdb->prefix . EBDN_TABLE_PRICE_FORMULA, array('id' => $id));
        }

        /**
         *
         */
        public static function recalcPos()
        {
            /** @var wpdb $wpdb */
            global $wpdb;
            $wpdb->query('UPDATE ' . $wpdb->prefix . EBDN_TABLE_PRICE_FORMULA . ' dest, (SELECT @r:=@r+1 as new_pos, z.id from(select id from wp_ebdn_price_formula order by pos) z, (select @r:=0)y) src SET dest.pos = src.new_pos where dest.id=src.id;');
        }

        /**
         * @param EBDN_Goods $goods
         * @param bool $single
         * @return array
         */
        public static function getGoodsFormula($goods, $single = true)
        {
            $res_formula_list = array();
            $formula_list = EBDN_PriceFormula::loadFormulasList();

            foreach ($formula_list as $formula) {
                $check = true;

                if (isset($formula->min_price) && $formula->min_price && (float)$formula->min_price >= (float)$goods->user_price) {
                    $check = false;
                }

                if (isset($formula->max_price) && $formula->max_price && (float)$formula->max_price <= (float)$goods->user_price) {
                    $check = false;
                }

                if (isset($formula->type) && $formula->type && $formula->type !== $goods->type) {
                    $check = false;
                }

                if (isset($formula->category) && $formula->category && (int)$formula->category !== (int)$goods->link_category_id) {
                    $check = false;
                }

                if ($check) {
                    $res_formula_list[] = $formula;

                    if ($single) {
                        break;
                    }
                }
            }

            return $res_formula_list;
        }

        public static function loadFormulasList()
        {
            /** @var wpdb $wpdb */
            global $wpdb;

            $formula = array();

            $results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . EBDN_TABLE_PRICE_FORMULA . " ORDER BY pos");

            if ($results) {
                foreach ($results as $row) {
                    $f = new EBDN_PriceFormula();
                    $f->id = $row->id;
                    $f->pos = $row->pos;

                    $f_data = unserialize($row->formula);
                    foreach ($f_data as $field => $value) {
                        if (property_exists(get_class($f), $field)) {
                            $f->$field = $value;
                        }
                    }
                    $formula[] = $f;
                }
            }
            return $formula;
        }

        public static function applyFormula($price, $formula)
        {
            $result = $price;
            if ($formula->sign === '=') {
                $result = $formula->value;
            } else if ($formula->sign === '*') {
                $result *= $formula->value;
            } else if ($formula->sign === '+') {
                $result += $formula->value;
            }
            return round($result, 2);
        }

        /**
         * @param EBDN_Goods $goods
         * @param $formula
         * @return mixed
         */
        public static function calcRegularPrice(&$goods, $formula)
        {
            $discount = 0;

            $discount_perc = $goods->getProductMeta('discount_perc');
            if ($discount_perc || strlen(trim((string)$discount_perc)) > 0) {
                $discount = (int)$discount_perc;
            } else {
                if (isset($goods->additional_meta['original_discount']) && strlen(trim((string)$goods->additional_meta['original_discount'])) > 0) {
                    $discount = (int)$goods->additional_meta['original_discount'];
                }
                if (strlen(trim((string)$formula->discount1)) > 0 && strlen(trim((string)$formula->discount2)) > 0) {
                    if ((int)$formula->discount1 > (int)$formula->discount2) {
                        $discount = mt_rand((int)$formula->discount2, (int)$formula->discount1);
                    } else {
                        $discount = mt_rand((int)$formula->discount1, (int)$formula->discount2);
                    }
                } else if (trim((string)$formula->discount1) !== '' || trim((string)$formula->discount2) !== '') {
                    $discount = strlen(trim((string)$formula->discount1)) > 0 ? (int)$formula->discount1 : (int)$formula->discount2;
                }
            }

            $goods->additional_meta['discount_perc'] = $discount;
            $goods->user_regular_price = round(($goods->user_price * 100) / (100 - $discount), 2);

            return $goods;
        }

    }
}