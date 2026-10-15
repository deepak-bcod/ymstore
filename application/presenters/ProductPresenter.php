<?php

class ProductPresenter extends BasePresenter
{
    public function product_image($type = 'thumb'){
        if($type === 'thumb' && SHOP_ID === 1 && defined('IMGIX_URL')) {
            return $this->imgix_url($type);
        }

        $path = ['thumb' => PRODUCT_THUMB_IMG][$type] ?? PRODUCT_THUMB_IMG;

        if (!empty($this->base_image)) {
            return $path.$this->base_image;
        }

        return PRODUCT_DEFAULT_IMG;
    }

    public function display_list_price($return_html = false){
        return (new DisplayPricePresenter)($this, $return_html);
    }

    public function imgix_url($type){
        return IMGIX_URL . '/products/original/' . $this->base_image . '?auto=format&h=450&w=450&fit=fill&fill=blur';
    }

    public function get_regular_price(){
        $price_options = [
            ($this->product_type === 'configurable' && !empty($this->price_sorting_configurable)) ? (float)$this->price_sorting_configurable : null,
            !empty($this->webshop_price) ? (float)$this->webshop_price : null,
            !empty($this->min_price) ? (float)$this->min_price : null,
            !empty($this->price) ? (float)$this->price : null
        ];
        foreach ($price_options as $opt) {
            if (!empty($opt) && $opt > 0) {
                return (float)$opt;
            }
        }
        return 0.00;
    }

    public function get_active_promotion($now = null){
        if ($now === null) {
            $now = time();
        }

        // Product status check
        if ((isset($this->status) && (int)$this->status === 2) || (isset($this->remove_flag) && (int)$this->remove_flag === 1)) {
            return null;
        }

        $type = null;
        $label = '';
        $icon = '';
        $starts_at = 0;
        $ends_at = 0;

        // Daily Deal check
        if (!empty($this->daily_deals)) {
            $starts_at = !empty($this->daily_deal_starts_at) ? (int)$this->daily_deal_starts_at : 0;
            $ends_at = !empty($this->daily_deal_ends_at) ? (int)$this->daily_deal_ends_at : 0;

            if ($starts_at <= $now && ($ends_at === 0 || $ends_at > $now)) {
                $type = 'daily_deal';
                $label = 'DAILY DEAL';
                $icon = '★';
            }
        }

        // Flash Sale check (takes precedence or evaluated if daily deal is not active)
        if (!$type && !empty($this->flash_sale)) {
            $starts_at = !empty($this->flash_sale_starts_at) ? (int)$this->flash_sale_starts_at : 0;
            $ends_at = !empty($this->flash_sale_ends_at) ? (int)$this->flash_sale_ends_at : 0;

            if ($starts_at <= $now && ($ends_at === 0 || $ends_at > $now)) {
                $type = 'flash_sale';
                $label = 'FLASH SALE';
                $icon = '⚡';
            }
        }

        // Fallback: check special_price_from and special_price_to
        if (!$type && !empty($this->special_price) && (float)$this->special_price > 0) {
            $sp_from = !empty($this->special_price_from) ? (int)$this->special_price_from : 0;
            $sp_to = !empty($this->special_price_to) ? (int)$this->special_price_to : 0;
            if (($sp_from === 0 || $sp_from <= $now) && ($sp_to === 0 || $sp_to > $now)) {
                $type = 'special_price';
                $label = 'SPECIAL OFFER';
                $icon = '★';
                $starts_at = $sp_from;
                $ends_at = $sp_to;
            }
        }

        if (!$type) {
            return null;
        }

        $regular = $this->get_regular_price();
        $special = !empty($this->special_price) ? (float)$this->special_price : 0.00;

        // If special price is not less than regular price, it is not a valid discount
        if ($special <= 0 || ($regular > 0 && $special >= $regular)) {
            return null;
        }

        $discount_percent = 0;
        if ($regular > 0 && $special < $regular) {
            $discount_percent = round((($regular - $special) / $regular) * 100);
        }

        $remaining_seconds = $ends_at > $now ? ($ends_at - $now) : 0;

        return (object)[
            'type' => $type,
            'label' => $label,
            'icon' => $icon,
            'starts_at' => $starts_at,
            'ends_at' => $ends_at,
            'remaining_seconds' => $remaining_seconds,
            'regular_price' => $regular,
            'special_price' => $special,
            'discount_percent' => $discount_percent,
            'badge_grid_html' => '<span class="promo-badge promo-badge-' . ($type === 'flash_sale' ? 'flash-sale' : 'daily-deal') . '">' . $icon . ' ' . $label . '</span>',
            'badge_normal_html' => '<span class="promo-pill-normal promo-pill-' . ($type === 'flash_sale' ? 'flash-sale' : 'daily-deal') . '">' . $icon . ' ' . $label . '</span>',
            'discount_badge_html' => ($discount_percent > 0 ? '<span class="promo-discount-badge">-' . $discount_percent . '%</span>' : '')
        ];
    }

    public function get_stock_badge(){
        $qty = null;
        if (isset($this->available_qty) && is_numeric($this->available_qty)) {
            $qty = (int)$this->available_qty;
        } elseif (isset($this->qty) && is_numeric($this->qty)) {
            $qty = (int)$this->qty;
        } elseif (isset($this->stock_qty) && is_numeric($this->stock_qty)) {
            $qty = (int)$this->stock_qty;
        }

        if ($qty !== null && $qty > 0 && $qty <= 10) {
            return (object)[
                'qty' => $qty,
                'grid_html' => '<span class="promo-badge promo-badge-limited-stock">📦 LIMITED STOCK</span>',
                'normal_html' => '<span class="promo-stock-warning">⚠️ Only ' . $qty . ' left</span>'
            ];
        }
        return null;
    }

    public function display_product_badge(){
        // Check active promotion first
        $promo = $this->get_active_promotion();
        if ($promo) {
            return $promo->badge_grid_html;
        }

        $todayDate=date('Y-m-d');
        $startDate=$this->launch_date;
        $date=date('d-m-Y',$startDate);
        $days = (strtotime($todayDate) - $startDate) / (60 * 60 * 24);
        $badge='';
        if($days<=45){
            $badge='<span class="new-badge">New</span>';
        }
        return $badge;
    }

}

