<?php if($paginator->hasPages()): ?>
    <nav role="navigation" aria-label="<?php echo e(__('Pagination Navigation')); ?>" class="flex items-center justify-between pagination-nav">
        <div class="flex justify-between flex-1 sm:hidden">
            <?php if($paginator->onFirstPage()): ?>
                <span class="pagination-btn disabled">
                    <?php echo __('pagination.previous'); ?>

                </span>
            <?php else: ?>
                <a href="<?php echo e($paginator->previousPageUrl()); ?>" class="pagination-btn">
                    <?php echo __('pagination.previous'); ?>

                </a>
            <?php endif; ?>

            <?php if($paginator->hasMorePages()): ?>
                <a href="<?php echo e($paginator->nextPageUrl()); ?>" class="pagination-btn ms-2">
                    <?php echo __('pagination.next'); ?>

                </a>
            <?php else: ?>
                <span class="pagination-btn disabled ms-2">
                    <?php echo __('pagination.next'); ?>

                </span>
            <?php endif; ?>
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div class="pagination-summary">
                <p>
                    <?php echo __('Showing'); ?>

                    <?php if($paginator->firstItem()): ?>
                        <span><?php echo e($paginator->firstItem()); ?></span>
                        <?php echo __('to'); ?>

                        <span><?php echo e($paginator->lastItem()); ?></span>
                    <?php else: ?>
                        <?php echo e($paginator->count()); ?>

                    <?php endif; ?>
                    <?php echo __('of'); ?>

                    <span><?php echo e($paginator->total()); ?></span>
                    <?php echo __('results'); ?>

                </p>
            </div>

            <div class="pagination-controls">
                
                <?php if($paginator->onFirstPage()): ?>
                    <span class="pagination-icon disabled" aria-hidden="true">
                        &lsaquo;
                    </span>
                <?php else: ?>
                    <a href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev" class="pagination-icon" aria-label="<?php echo e(__('pagination.previous')); ?>">
                        &lsaquo;
                    </a>
                <?php endif; ?>

                
                <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    
                    <?php if(is_string($element)): ?>
                        <span class="pagination-gap"><?php echo e($element); ?></span>
                    <?php endif; ?>

                    
                    <?php if(is_array($element)): ?>
                        <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($page == $paginator->currentPage()): ?>
                                <span class="pagination-page is-active"><?php echo e($page); ?></span>
                            <?php else: ?>
                                <a href="<?php echo e($url); ?>" class="pagination-page" aria-label="<?php echo e(__('Go to page :page', ['page' => $page])); ?>">
                                    <?php echo e($page); ?>

                                </a>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                
                <?php if($paginator->hasMorePages()): ?>
                    <a href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next" class="pagination-icon" aria-label="<?php echo e(__('pagination.next')); ?>">
                        &rsaquo;
                    </a>
                <?php else: ?>
                    <span class="pagination-icon disabled" aria-hidden="true">
                        &rsaquo;
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </nav>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/vendor/pagination/tailwind.blade.php ENDPATH**/ ?>