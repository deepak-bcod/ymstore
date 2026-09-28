<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


$app->get('/webshop/get_blog_nextpre_blog/{blog_id}', function (Request $request, Response $response, $args) {

    $blog_id = $args['blog_id'];
    $error = '';
    if ($blog_id == '') {
        $error = 'Please pass all the mandatory values';
    } else {
        $webshop_obj = new DbBlogFeature();

        $get_blog_details = $webshop_obj->getPrevNextBlogDetails($blog_id);
        $final_arr = array();

        if ($get_blog_details == false) {
            $error = 'No result found';
        } else {

            foreach ($get_blog_details as $value) {
                if ($blog_id > $value['id']) {
                    $arr['prev_arr'] = $value;
                } else if ($blog_id < $value['id']) {

                    $arr['next_arr'] = $value;
                }
                $final_arr = $arr;
            }
        }
    }
    if ($error != '') {
        $message['statusCode'] = '500';
        $message['is_success'] = 'false';
        $message['message'] = $error;
        exit(json_encode($message));
    } else {
        $message['statusCode'] = '200';
        $message['is_success'] = 'true';
        $message['message'] = 'Prev Next Blog result';
        $message['BlogPrevNexList'] = $final_arr;
        exit(json_encode($message));
    }
});

$app->post('/webshop/blog_detail', function (Request $request, Response $response, $args) {
    $data = $request->getParsedBody();
    extract($data);
    $error = '';
    if ($url_key == '') {
        $error = 'Please pass all the mandatory values';
    } else {
        // $message['statusCode'] = '200';
        // $message['is_success'] = 'true';
        // $message['message'] = 'Blog available';
        // $message['blogData'] = $url_key;
        // exit(json_encode($message));
        $webshop_obj = new DbBlogFeature();
        $product_obj = new DbProductFeature();
        $blogArray = [];
        $finalArray = [];
        $variants_arr = [];
        $blog_details = $webshop_obj->blogDetails($url_key);
        
        if ($blog_details == false) {
            $error = 'Blog not available';
        }
        // ✅ DIRECTLY RETURN BLOG DETAILS
    $finalArray = $blog_details;
       

    }

    if ($error != '') {
        $message['statusCode'] = '500';
        $message['is_success'] = 'false';
        $message['message'] = $error;
        exit(json_encode($message));
    } else {
        $message['statusCode'] = '200';
        $message['is_success'] = 'true';
        $message['message'] = 'Blog available';
        $message['blogData'] = $finalArray;
        exit(json_encode($message));
    }
});

$app->post('/webshop/get_blog_listing', function (Request $request, Response $response, $args) {
    $data = $request->getParsedBody();
    extract($data);
    $error = '';
    $webshop_obj = new DbBlogFeature();
    $page = $page ?? 0;
    $page_size = $page_size ?? 0;

    // if ($page > 0) {
    //     $page = ($page - 1) * $page_size;
    // }
	// print_r($page);
	// print_r($page_size);die;

    $blogData = $webshop_obj->getBlogListing($page, $page_size);
    // echo "<pre>";
	// print_r($blogData);die;
    $countBlogData = count($blogData);
    if ($blogData == false) {
        $error = 'Blog not available';
    } else {
        $blogData = $blogData;
    }
    if ($error != '') {
        $message['statusCode'] = '500';
        $message['is_success'] = 'false';
        $message['message'] = $error;
        exit(json_encode($message));
    } else {
        $message['statusCode'] = '200';
        $message['is_success'] = 'true';
        $message['message'] = 'Blog available';
        $message['blogData'] = $blogData;
        $message['BlogDataCount'] = $countBlogData;
        exit(json_encode($message));
    }
});
