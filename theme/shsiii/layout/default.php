<?php

global $DB;
$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-pre', $OUTPUT));
$hassidepost = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-post', $OUTPUT));
$haslogininfo = (empty($PAGE->layout_options['nologininfo']));

$showsidepre = ($hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT));
$showsidepost = ($hassidepost && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT));

$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));

$hasfootnote = (!empty($PAGE->theme->settings->footnote));

$courseheader = $coursecontentheader = $coursecontentfooter = $coursefooter = '';
if (empty($PAGE->layout_options['nocourseheaderfooter'])) {
    $courseheader = $OUTPUT->course_header();
    $coursecontentheader = $OUTPUT->course_content_header();
    if (empty($PAGE->layout_options['nocoursefooter'])) {
        $coursecontentfooter = $OUTPUT->course_content_footer();
        $coursefooter = $OUTPUT->course_footer();
    }
}

$bodyclasses = array();
if ($showsidepre && !$showsidepost) {
    if (!right_to_left()) {
        $bodyclasses[] = 'side-pre-only';
    } else {
        $bodyclasses[] = 'side-post-only';
    }
} else if ($showsidepost && !$showsidepre) {
    if (!right_to_left()) {
        $bodyclasses[] = 'side-post-only';
    } else {
        $bodyclasses[] = 'side-pre-only';
    }
} else if (!$showsidepost && !$showsidepre) {
    $bodyclasses[] = 'content-only';
}
if ($hascustommenu) {
    $bodyclasses[] = 'has_custom_menu';
}

///////////////////////////////////////////////////////////////////

            $courses  = enrol_get_my_courses();
            $colcount = 4;
            $colmax   = 0;
            if (is_array($courses) && (count($courses) > 0)) {
              usort($courses, function($a, $b) {
                return strcasecmp($a->fullname, $b->fullname);
              });
              $colmax   = ceil(count($courses) / $colcount);
            }


///////////////////////////////////////////////////////////////////

/*echo $OUTPUT->doctype()*/
?><!DOCTYPE html>
<?php $OUTPUT->doctype(); ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title><?php echo $PAGE->title ?></title>
    <meta name="robots" content="noindex, nofollow" />
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
  <!--[if lt IE 9]>
    <script type="text/javascript">
      (function(){if(!/*@cc_on!@*/0)return;var e = "abbr,article,aside,audio,canvas,datalist,details,eventsource,figure,footer,header,hgroup,mark,menu,meter,nav,output,progress,section,time,video".split(','),i=e.length;while(i--){document.createElement(e[i])}})()
    </script>
    <style type="text/css">
      article, aside, figure, footer, header, hgroup, nav, section {display: block;}
    </style>
  <![endif]-->


    <?php echo $OUTPUT->standard_head_html() ?>
</head>

<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">
<?php echo $OUTPUT->standard_top_of_body_html() ?>

<?php if ($hasheading || $hasnavbar): ?>

<header id="wide-header">
  <div id="topbar">
    <div class="container">
      <!--<div class="search">
      </div>-->
      <!-- <?php echo "Heading: $hasheading & Navbar:$hasnavbar"; ?> -->
    </div>
  </div>


  <div id="topnav">
    <div class="container">
      <h1 id="sitelogo" class="moodle">
        <a class="logo" href="<?php echo $CFG->wwwroot; ?>">
          <img src="<?php echo $OUTPUT->pix_url('images/blank', 'theme'); ?>" alt="<?php print_string('home'); ?>">
        </a>
      </h1>


      <div class="userblock" >
        <? /* adapted from $PAGE->login_info() */ ?>
        <?php if (isloggedin()): ?>
          <p class="userblock-salutation">
            Hello
            <span class="userblock-name">
              <?php echo $USER->firstname; ?>
            </span>
            <? if (\core\session\manager::is_loggedinas()) : ?>
              <? $realuser = \core\session\manager::get_realuser(); ?>
              (really <?php echo $realuser->firstname; ?>)
            <? endif; ?>
          </p>

          <ul class="menu">
            <? $rolename = ''; ?>
            <? /*$context = get_context_instance(CONTEXT_COURSE, $COURSE->id);*/ ?>
            <? $context = context_course::instance($COURSE->id); ?>
            <? if ($role = @$DB->get_record('role', array('id'=>$USER->access['rsw'][$context->path]))) $rolename = format_string($role->name); ?>


            <?php if (is_role_switched($COURSE->id)): ?>
              <li>            
              <a href="<?php echo $CFG->wwwroot . '/course/view.php?id='.$COURSE->id.'&amp;switchrole=0&amp;sesskey='.sesskey(); ?>">
                <b><?php echo get_string('switchrolereturn'); ?></b>
              </a>
              </li>
            <?php endif; ?>

            <? if (\core\session\manager::is_loggedinas()) : ?>
              <? $realuser = \core\session\manager::get_realuser(); ?>
              <li>              
              <a href="<?php echo $CFG->wwwroot . '/course/loginas.php?id='.$COURSE->id.'&amp;sesskey='.sesskey(); ?>">
                <b>Stop impersonating <?php echo $USER->firstname; ?></b>
              </a>
              </li>
            <? endif; ?>              
            
            

            <li>
              <a href="<?php echo $CFG->wwwroot . '/user/profile.php?id='.$USER->id; ?>">
                Update your profile
              </a>
            </li>
            
            <? if (!\core\session\manager::is_loggedinas()) : ?>
              <?php if ($PAGE->theme->settings->password_reset): ?>
                <li>
                  <a href="<?php echo $PAGE->theme->settings->password_reset; ?>">
                    Change password
                  </a>
                </li>
              <?php endif; ?>
              <li>
                <a href="<?php echo $CFG->wwwroot . '/login/logout.php?sesskey='.sesskey(); ?>">
                  <?php echo get_string('logout'); ?>
                </a>
              </li>
            <? endif; ?>
          </ul>
        <?php else: ?>
          <p class="userblock-salutation">
            Welcome to the site
          </p>
        <?php endif; ?>


      </div>

      <div class="mainlinks">
        <!--<div id="navcontainer">-->
          <?php if ($hascustommenu && isloggedin()) : ?>
            <!--<div id="custommenu" class="javascript-disabled">-->
            <?php echo $custommenu; ?>
            <!--</div>-->
          <?php endif; ?>
        <!--</div>-->
      </div>
    </div>
  </div>

  <?php if ($hasnavbar): ?>
  <section id="fullnavblock">
    <div class="container" id="page-header"><? /* page-header is a critical Moodle element */ ?>
      <div class="helpresponse-block">
        <?php if (!empty($PAGE->layout_options['langmenu'])): ?>
          <?php echo $OUTPUT->lang_menu(); ?>
        <?php endif; ?>

        <div class="navbutton"> <?php echo $PAGE->button; ?> </div>
      </div>

      <div class="module-header">
        <?php if (is_array($courses) && (count($courses) > 0)) : ?>
          <div id="slide-block-container">
            <a href="#navigate" id="slide-block-header">My Moodle Courses</a>
          </div>
        <?php endif; ?>
      </div>
      <div id="slide-block">
        <? /* course menu display */ ?>
        <div class="navcontainer">
          <?php for ($i = 0; $i < $colcount; $i++): ?>
            <ul class="menu">
              <?php for ($j = 0; $j < $colmax; $j++): ?>
                <?php $idx = ($j * $colcount) + $i; ?>
                <?php if ($idx < count($courses)): ?>
                  <li>
                    <a href="<?php echo $CFG->wwwroot . '/course/view.php?id='.$courses[$idx]->id; ?>">
                      <?php echo $courses[$idx]->fullname; ?>
                    </a>
                  </li>
                <?php endif; ?>
              <?php endfor; ?>
            </ul>
          <?php endfor; ?>
        </div>
      </div>
      <div class="clr"></div>
    </div>
  </section>
  <?php endif; /* has (navbar) */ ?>
</header>

<?php endif; /* has (header || navbar) */ ?>

<div id="page-wrapper">
  <div id="page">
<!-- END OF HEADER -->
<!-- START CUSTOMMENU AND NAVBAR -->

        <?php if (!empty($courseheader)) { ?>
            <div id="course-header"><?php echo $courseheader; ?></div>
        <?php } ?>

      <?php if (count($PAGE->navbar->get_items()) > 1): ?>
        <?php if ($hasnavbar) { ?>
            <div class="navbar clearfix">
                <div class="breadcrumb"><?php echo $OUTPUT->navbar(); ?></div>
                <? /* <div class="navbutton"> <?php echo $PAGE->button; ?></div> */ ?>
            </div>
        <?php } ?>
      <?php endif; ?>

<!-- END OF CUSTOMMENU AND NAVBAR -->
    <div id="page-content">
       <div id="region-main-box">
           <div id="region-pre-box">
               <div id="region-main">
                   <div class="region-content">
                       <?php if ($COURSE->id > 1) : ?>
                         <h2 class="coursetitle">
                           <?php echo $COURSE->fullname; ?>
                         </h2>
                       <?php endif; ?>

                       <?php echo $coursecontentheader; ?>
                       <?php echo $OUTPUT->main_content() ?>
                       <?php echo $coursecontentfooter; ?>
                   </div>
               </div>

               <?php if ($hassidepre OR (right_to_left() AND $hassidepost)) { ?>
               <div id="region-pre" class="block-region">
                   <div class="region-content">
                           <?php
                       if (!right_to_left()) {
                           echo $OUTPUT->blocks_for_region('side-pre');
                       } elseif ($hassidepost) {
                           echo $OUTPUT->blocks_for_region('side-post');
                   } ?>

                   </div>
               </div>
               <?php } ?>

               <?php if ($hassidepost OR (right_to_left() AND $hassidepre)) { ?>
               <div id="region-post" class="block-region">
                   <div class="region-content">
                          <?php
                      if (!right_to_left()) {
                          echo $OUTPUT->blocks_for_region('side-post');
                      } elseif ($hassidepre) {
                          echo $OUTPUT->blocks_for_region('side-pre');
                   } ?>
                   </div>
               </div>
               <?php } ?>

            </div>
        </div>
    </div>

    <!-- START OF FOOTER -->
    <?php /* if (!empty($coursefooter)) { ?>
        <div id="course-footer"><?php echo $coursefooter; ?></div>
    <?php } */ ?>
    <div class="clearfix"></div>
  </div>
</div>
<?php if ($hasfooter) { ?>
  <footer id="footer">
    <div class="container" id="page-footer"><? /* page-footer is a critical Moodle element */ ?>
      <?php if ($haslogininfo): ?>
        <?php echo $OUTPUT->login_info(); ?>
      <?php endif; ?>
      <p>
        &copy; <?php echo date('Y');?> <a href="http://www.sydneyboyshigh.com">Sydney Boys High School</a>
      </p>
      <?php echo $OUTPUT->standard_footer_html(); ?>
    </div>
  </footer>
<?php } ?>

<?php if ($hasnavbar): ?>

<script type="text/javascript">
  YUI().use('node', 'event', 'anim', function (Y) {
    Y.on('domready', function(e) {
      function dockAlign(e) {
        if (Y.one('#dock')) {
          Y.one('#dock').setStyle('left', (Y.one('#page').get('offsetLeft') - Y.one('#dock').get('offsetWidth')) + 'px');
        }
      }

      Y.one('#slide-block').setStyle('height', '0px');
      if (Y.one('#slide-block-header')) {
        var expandOffset = 0;
        if (Y.UA.ie > 0) expandOffset = 10;

        var fxSlide = new Y.Anim({
          node: '#slide-block',
          to: { height: 5000 },
          easing: 'easeBoth',
          duration: 0.2
        });

        Y.one('#slide-block-container').setStyle('cursor', 'pointer');
        Y.one('#slide-block-container').on('click', function(e) {
          fxSlide.stop();
          e.preventDefault();
          var link = Y.one('#slide-block-header');
          (!link.hasClass('expanded')) ? fxSlide.set('to.height', Y.one('#slide-block').get('scrollHeight')+expandOffset) : fxSlide.set('to.height', 0);
          fxSlide.run();
          link.hasClass('expanded') ? link.removeClass('expanded') : link.addClass('expanded');
        });

      }

      // fix position of the core dock
      //Y.on('windowresize', dockAlign);
      //if (M.core_dock) {
      //  M.core_dock.on('dock:shown', dockAlign);
      //}
    });
  });
</script>
<?php endif; /* has (navbar) */ ?>

<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>
