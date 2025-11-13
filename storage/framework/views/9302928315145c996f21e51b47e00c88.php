<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e(__('Редактирование аукционного объявления')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <?php if($errors->any()): ?>
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                            <p class="font-bold"><?php echo e(__('Обнаружены ошибки:')); ?></p>
                            <ul>
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo e(route('auction-listings.update', $listing)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900"><?php echo e(__('Основная информация')); ?></h3>
                                <p class="text-sm text-gray-500"><?php echo e(__('Вы можете изменить только цену и описание для аукционного объявления.')); ?></p>
                            </div>

                            <!-- Title (read-only) -->
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700"><?php echo e(__('Заголовок')); ?></label>
                                <input type="text" id="title" value="<?php echo e($listing->title); ?>" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 cursor-not-allowed">
                            </div>

                            <!-- Vehicle Details (read-only) -->
                            <?php if($listing->vehicleDetail): ?>
                                <div class="p-4 bg-gray-50 rounded-lg border">
                                    <h4 class="font-medium text-gray-800 mb-2"><?php echo e(__('Характеристики (нередактируемые)')); ?></h4>
                                    <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                        <dt class="text-gray-500"><?php echo e(__('Марка:')); ?></dt><dd class="text-gray-900"><?php echo e($listing->vehicleDetail->make); ?></dd>
                                        <dt class="text-gray-500"><?php echo e(__('Модель:')); ?></dt><dd class="text-gray-900"><?php echo e($listing->vehicleDetail->model); ?></dd>
                                        <dt class="text-gray-500"><?php echo e(__('Год:')); ?></dt><dd class="text-gray-900"><?php echo e($listing->vehicleDetail->year); ?></dd>
                                        <dt class="text-gray-500"><?php echo e(__('Пробег:')); ?></dt><dd class="text-gray-900"><?php echo e(number_format($listing->vehicleDetail->mileage)); ?> <?php echo e(__('км')); ?></dd>
                                    </dl>
                                    <a href="<?php echo e($listing->vehicleDetail->source_auction_url); ?>" target="_blank" class="text-blue-600 hover:underline text-sm mt-2 inline-block"><?php echo e(__('Посмотреть на аукционе')); ?></a>
                                </div>
                            <?php endif; ?>

                            <!-- Price (editable) -->
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700"><?php echo e(__('Цена (AMD)')); ?></label>
                                <input type="number" name="price" id="price" value="<?php echo e(old('price', $listing->price)); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <!-- Description (editable) -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700"><?php echo e(__('Описание')); ?></label>
                                <textarea name="description" id="description" rows="5" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><?php echo e(old('description', $listing->description)); ?></textarea>
                                <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <!-- Images (read-only) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700"><?php echo e(__('Фотографии')); ?></label>
                                <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <?php $__empty_1 = true; $__currentLoopData = $listing->getMedia('images'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <div class="relative">
                                            <img src="<?php echo e($media->getUrl('thumb')); ?>" alt="<?php echo e(__('Фото')); ?>" class="w-full h-32 object-cover rounded-lg">
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <p class="text-gray-500 col-span-full"><?php echo e(__('Фотографии отсутствуют.')); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end space-x-3">
                            <a href="<?php echo e(route('dashboard.my-auctions')); ?>" class="bg-gray-200 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-300">
                                <?php echo e(__('Отмена')); ?>

                            </a>
                            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                                <?php echo e(__('Сохранить изменения')); ?>

                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/auction_listings/edit.blade.php ENDPATH**/ ?>