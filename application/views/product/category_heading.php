<div class="pull-left">
    <h1>
        <?php
        // 1. Ensure the language is detected
        $currentLang = isset($_SESSION['site_lang']) ? $_SESSION['site_lang'] : 'en';

        // 2. Use your $lang array for the label
        // This assumes that the $lang array is ALREADY LOADED in your file
        $categoryLabel = isset($lang['category_label']) ? $lang['category_label'] : 'CATEGORY :  ';
        echo $this->lang->line('category_label'); 

        // 3. Build the category path
        $displayCategory = '';
        if($currentLang == "french"){
            if (isset($main_cat) && !empty($main_cat)) { $displayCategory = $main_lang_title; }
            if (isset($level1_cat) && !empty($level1_cat)) { $displayCategory .= ' / ' . $level1_lang_title; }
            if (isset($level2_cat) && !empty($level2_cat)) { $displayCategory .= ' / ' . $level2_lang_title; }
        }else{
            if (isset($main_cat) && !empty($main_cat)) { $displayCategory = $main_cat_name; }
            if (isset($level1_cat) && !empty($level1_cat)) { $displayCategory .= ' / ' . $level1_cat_name; }
            if (isset($level2_cat) && !empty($level2_cat)) { $displayCategory .= ' / ' . $level2_cat_name; }
        }
        //  echo '<pre>';
        // print_r([
        //     'main_cat_name'   => $main_cat_name ?? '',
        //     'level1_cat_name' => $level1_cat_name ?? '',
        //     'level2_cat_name' => $level2_cat_name ?? '',
        //     'displayCategory' => $displayCategory
        // ]);
        // die();

        // echo ' ' . $displayCategory;

        // 4. Handle Subscription using your $lang array
        $subscriptionCategories = ['Childrens Magazines', 'INTERNATIONAL MAGAZINES'];
        foreach ($subscriptionCategories as $cat) {
            if (strpos($displayCategory, $cat) !== false) {
                // Use the subscription label from your array
                $subText = isset($lang['subscription_label']) ? $lang['subscription_label'] : 'Subscription';
                $displayCategory = ucwords(strtolower($cat)) . ' ' . $subText;
                break;
            }
        }
        echo ' ' . $displayCategory;
        ?>
    </h1>
</div>