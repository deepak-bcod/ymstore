<?php $this->load->view('common/header'); ?>

 <style>
        body {
            font-family: Arial, sans-serif;
            background: #f6f8fa;
            margin: 0;
            padding: 0;
        }
        .thank-you-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .thank-you-box {
            background: #fff;
            padding: 40px 30px;
            border-radius: 10px;
            text-align: center;
            max-width: 420px;
            width: 90%;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }
        .thank-you-box h1 {
            color: #28a745;
            margin-bottom: 10px;
        }
        .thank-you-box p {
            color: #555;
            font-size: 16px;
        }
        .thank-you-box .icon {
            font-size: 60px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="thank-you-container">
    <div class="thank-you-box">
        <?php if (!empty($already_submitted) || $this->session->flashdata('already_submitted')): ?>
            <div class="icon">ℹ️</div>
            <h1><?= !empty($this->lang->line('review_already_submitted_title')) ? $this->lang->line('review_already_submitted_title') : 'Review Already Submitted'; ?></h1>
            <p><?= !empty($this->lang->line('review_already_submitted_msg')) ? $this->lang->line('review_already_submitted_msg') : 'You have already submitted a review for this order. Thank you!'; ?></p>
        <?php else: ?>
            <div class="icon">✅</div>
            <h1><?= $this->lang->line('thank_you_title'); ?></h1>
            <p><?= $this->lang->line('thank_you_review_msg'); ?></p>
            <p><?= $this->lang->line('thank_you_feedback_msg'); ?></p>
        <?php endif; ?>
    </div>
</div>

 <?php $this->load->view('common/footer'); ?>

<style>

.review-form {
    border:1px solid #eee;
    padding: 30px 30px;
    max-width: 430px;
    margin: 60px auto;
    text-align: center;
    font: 14px "Open Sans", sans-serif;
    background:#f8f8f8;
}

.review-form h3 {
    font-size: 20px;
    font-family: Roboto;
    font-weight: 500;
    color: #222;
}

.review-form textarea {
    width: 100%;
    height: 80px;
    padding: 7px;
     font: 14px "Open Sans", sans-serif;
}

.star-rating {
    direction: rtl; /* so highest star comes first */
    display: inline-flex;
    display: block;
    margin-bottom: 15px;
    margin-top: -6px;
}
.star-rating input {
    display: none;
}
.star-rating label {
    font-size: 31px;
    color: #ccc;
    cursor: pointer;
    padding: 0 0px;
}
.star-rating input:checked ~ label,
.star-rating label:hover,
.star-rating label:hover ~ label {
    color: gold;
}

.review-form button[type="submit"] {
       background: #0f5cd0;
    color: #fff;
    font-size: 14px !important;
    font-weight: 400;
    padding: 12px 0 !important;
    margin-top: 20px !important;
    max-width: 130px;
    width: 100%;
    border: 0 !IMPORTANT;
    cursor: pointer;
    line-height: 10px;
}
</style>
