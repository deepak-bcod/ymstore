<?php
$site_lang = $this->session->userdata('site_lang') ?? '';
$lcode = $this->session->userdata('lcode') ?? '';
$is_french = ($site_lang === 'french' || $lcode === 'fr');

$mainCategory = (isset($categoryList->is_success) && $categoryList->is_success === 'true' && !empty($categoryList->CategoryDetails)) ? $categoryList->CategoryDetails : null;
$subCategory = (isset($categoryListSub->is_success) && $categoryListSub->is_success === 'true' && !empty($categoryListSub->CategoryDetails)) ? $categoryListSub->CategoryDetails : null;
$childCategory = (isset($categoryListChild->is_success) && $categoryListChild->is_success === 'true' && !empty($categoryListChild->CategoryDetails)) ? $categoryListChild->CategoryDetails : null;

// Level 0: Main / Parent Category
if (!empty($mainCategory) && !empty($mainCategory->cat_name)) {
    $mainName = ($is_french && !empty($mainCategory->lang_title)) ? $mainCategory->lang_title : $mainCategory->cat_name;
    $mainUrl  = base_url() . 'category/' . $mainCategory->slug;
    ?>
    <li>
        <a href="<?php echo $mainUrl; ?>"><?php echo $mainName; ?></a>
    </li>
    <?php
}

// Level 1: Subcategory
if (!empty($mainCategory) && !empty($subCategory) && !empty($subCategory->cat_name)) {
    $subName = ($is_french && !empty($subCategory->lang_title)) ? $subCategory->lang_title : $subCategory->cat_name;
    $subUrl  = base_url() . 'category/' . $mainCategory->slug . '/' . $subCategory->slug;
    ?>
    <li>
        <a href="<?php echo $subUrl; ?>"><?php echo $subName; ?></a>
    </li>
    <?php
}

// Level 2: Child Category
if (!empty($mainCategory) && !empty($subCategory) && !empty($childCategory) && !empty($childCategory->cat_name)) {
    $childName = ($is_french && !empty($childCategory->lang_title)) ? $childCategory->lang_title : $childCategory->cat_name;
    $childUrl  = base_url() . 'category/' . $mainCategory->slug . '/' . $subCategory->slug . '/' . $childCategory->slug;
    ?>
    <li>
        <a href="<?php echo $childUrl; ?>"><?php echo $childName; ?></a>
    </li>
    <?php
}
?>