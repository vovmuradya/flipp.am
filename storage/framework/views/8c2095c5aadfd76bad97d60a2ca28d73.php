<?php
    $fieldName = $fieldName ?? 'region_id';
    $fieldId = $fieldId ?? $fieldName;
    $selectedRegion = $selectedRegion ?? null;
    $isRequired = $required ?? true;
    $regionsCollection = $regions instanceof \Illuminate\Support\Collection ? $regions : collect($regions);
    $regionsByParent = $regionsCollection->groupBy('parent_id');
    $country = $regionsCollection->firstWhere('type', 'country');
    $capitalCities = $country ? ($regionsByParent->get($country->id) ?? collect())->where('type', 'city')->sortBy('localized_name') : collect();
    $districts = $regionsCollection->where('type', 'district')->sortBy('localized_name');
?>

<div class="<?php echo e($wrapperClass ?? 'mb-4'); ?>">
    <label class="form-label">
        <?php echo e($label ?? __('Регион')); ?>

        <?php if($isRequired): ?>
            <span class="text-danger">*</span>
        <?php endif; ?>
    </label>
    <select
        name="<?php echo e($fieldName); ?>"
        id="<?php echo e($fieldId); ?>"
        class="form-select"
        <?php if($isRequired): ?> required <?php endif; ?>
    >
        <option value=""><?php echo e(__('Выберите регион')); ?></option>

        <?php if($capitalCities->isNotEmpty()): ?>
            <optgroup label="<?php echo e(__('Столица')); ?>">
                <?php $__currentLoopData = $capitalCities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $capital): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($capital->id); ?>" <?php if($selectedRegion == $capital->id): echo 'selected'; endif; ?>>
                        <?php echo e($capital->localized_name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </optgroup>
        <?php endif; ?>

        <?php $__currentLoopData = $districts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $district): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $cities = ($regionsByParent->get($district->id) ?? collect())
                    ->where('type', 'city')
                    ->sortBy('localized_name');
            ?>
            <optgroup label="<?php echo e($district->localized_name); ?>">
                <?php $__empty_1 = true; $__currentLoopData = $cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <option value="<?php echo e($city->id); ?>" <?php if($selectedRegion == $city->id): echo 'selected'; endif; ?>>
                        <?php echo e($city->localized_name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <option value="<?php echo e($district->id); ?>" <?php if($selectedRegion == $district->id): echo 'selected'; endif; ?>>
                        <?php echo e($district->localized_name); ?>

                    </option>
                <?php endif; ?>
            </optgroup>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>

    <?php $__errorArgs = [$fieldName];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <small class="text-danger mt-1 d-block"><?php echo e($message); ?></small>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>
<?php /**PATH /var/www/html/resources/views/listings/partials/region-dropdown.blade.php ENDPATH**/ ?>