<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title><?php echo e($page['title']); ?></title>

    <link rel="stylesheet" href="css/style.css" />
    <script src="js/all.js"></script>


    <?php if(isset($page['language_tabs'])): ?>
      <script>
        $(function() {
            setupLanguages(<?php echo json_encode($page['language_tabs']); ?>);
        });
      </script>
    <?php endif; ?>
  </head>

  <body class="">
    <a href="#" id="nav-button">
      <span>
        NAV
        <img src="images/navbar.png" />
      </span>
    </a>
    <div class="tocify-wrapper">
        <img src="images/logo.png" />
        <?php if(isset($page['language_tabs'])): ?>
            <div class="lang-selector">
                <?php $__currentLoopData = $page['language_tabs']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <a href="#" data-language-name="<?php echo e($lang); ?>"><?php echo e($lang); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
        <?php if(isset($page['search'])): ?>
            <div class="search">
              <input type="text" class="search" id="input-search" placeholder="Search">
            </div>
            <ul class="search-results"></ul>
        <?php endif; ?>
      <div id="toc">
      </div>
        <?php if(isset($page['toc_footers'])): ?>
            <ul class="toc-footer">
                <?php $__currentLoopData = $page['toc_footers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <li><?php echo $link; ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        <?php endif; ?>
    </div>
    <div class="page-wrapper">
      <div class="dark-box"></div>
      <div class="content">
          <?php echo $content; ?>

      </div>
      <div class="dark-box">
          <?php if(isset($page['language_tabs'])): ?>
              <div class="lang-selector">
                <?php $__currentLoopData = $page['language_tabs']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="#" data-language-name="<?php echo e($lang); ?>"><?php echo e($lang); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </div>
          <?php endif; ?>
      </div>
    </div>
  </body>
</html><?php /**PATH /Users/pim/Iconize/Dev/Beep/vendor/mpociot/documentarian/resources/views/index.blade.php ENDPATH**/ ?>