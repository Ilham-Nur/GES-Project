<?php if (isset($component)) { $__componentOriginal23a33f287873b564aaf305a1526eada4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal23a33f287873b564aaf305a1526eada4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout','data' => ['dataPtges' => $dataPtges ?? '','wa' => $wa ?? '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['dataPtges' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($dataPtges ?? ''),'wa' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($wa ?? '')]); ?>
  <?php $__env->startSection('title', 'PT. GES | About Us'); ?>

  <body class="navigasi-page">
    <div id="About" class="JudulAbout">
      <h2>About Us</h2>
    </div>

    <?php if($dataPtges): ?>
    <div class="content-container">
      <div class="contentAbout">
        <h2>What they say about us</h2>
        <p id="parafAbout">
          <?php echo nl2br(e($dataPtges->Paragraph_AboutUs)); ?>

        </p>
      </div>
      <div class="imageAbout" id="imageAbout">
        <img src="<?php echo e(asset('storage/images/' . $dataPtges->Image_AboutUs)); ?>" alt="About Us Image" style="border-radius:30px;" loading="lazy" class="lazyload">
      </div>
    </div>
    <?php else: ?>
    <div class="content-container">
      <p class="text-secondary">No Content Available</p>
      <div class="imageAbout" id="imageAbout">
        <img src="<?php echo e(asset('/img/default.jpg')); ?>" alt="About Us Image" style="border-radius:30px; height:350px; width:500px;">
      </div>
    </div>
    <?php endif; ?>
  </body>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal23a33f287873b564aaf305a1526eada4)): ?>
<?php $attributes = $__attributesOriginal23a33f287873b564aaf305a1526eada4; ?>
<?php unset($__attributesOriginal23a33f287873b564aaf305a1526eada4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal23a33f287873b564aaf305a1526eada4)): ?>
<?php $component = $__componentOriginal23a33f287873b564aaf305a1526eada4; ?>
<?php unset($__componentOriginal23a33f287873b564aaf305a1526eada4); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\GES\GES-Project\resources\views\landingpage\About.blade.php ENDPATH**/ ?>