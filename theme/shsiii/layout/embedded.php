<!DOCTYPE html>
<?php $OUTPUT->doctype(); ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title><?php echo $PAGE->title ?></title>
    <meta name="robots" content="noindex, nofollow" />
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
</head>
<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses) ?>">
<?php echo $OUTPUT->standard_top_of_body_html() ?>

<div id="page">

<!-- END OF HEADER -->

    <div id="content" class="clearfix">
        <?php echo $OUTPUT->main_content() ?>
    </div>

<!-- START OF FOOTER -->
</div>
<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>