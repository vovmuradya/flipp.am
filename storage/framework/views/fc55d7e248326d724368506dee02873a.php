<?php
    $supportedLocales = config('app.supported_locales', []);
    $localeLabels = config('app.locale_labels', []);
    $localeOptions = collect($supportedLocales)->mapWithKeys(function ($code) use ($localeLabels) {
        return [
            $code => [
                'label' => $localeLabels[$code]['label'] ?? strtoupper($code),
                'description' => match ($code) {
                    'hy' => __('Армянская версия'),
                    'ru' => __('Русская версия'),
                    'en' => __('English version'),
                    default => strtoupper($code),
                },
            ],
        ];
    });
?>

<div id="localeModal" class="locale-modal" aria-hidden="true">
    <div class="locale-modal__backdrop" data-locale-modal-close></div>
    <div class="locale-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="localeModalTitle">
        <button type="button" class="locale-modal__close" data-locale-modal-close aria-label="<?php echo e(__('Закрыть')); ?>">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="locale-modal__header">
            <h2 id="localeModalTitle"><?php echo e(__('Выберите язык')); ?></h2>
            <p><?php echo e(__('Продолжайте на удобном языке — мы запомним ваш выбор.')); ?></p>
        </div>
        <div class="locale-modal__body">
            <?php $__currentLoopData = $localeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <form method="POST" action="<?php echo e(route('locale.update')); ?>" class="locale-modal__form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="locale" value="<?php echo e($code); ?>">
                    <button type="submit" class="locale-modal__option" data-locale-option="<?php echo e($code); ?>">
                        <span class="locale-modal__option-title"><?php echo e($option['label']); ?></span>
                        <span class="locale-modal__option-desc"><?php echo e($option['description']); ?></span>
                        <?php if(app()->getLocale() === $code): ?>
                            <span class="locale-modal__option-badge"><?php echo e(__('Текущий')); ?></span>
                        <?php endif; ?>
                    </button>
                </form>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="locale-modal__footer">
            <button type="button" class="btn btn-outline-secondary w-100" data-locale-modal-close>
                <?php echo e(__('Продолжить позже')); ?>

            </button>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('localeModal');
            if (!modal) {
                return;
            }

            const openModal = () => {
                modal.setAttribute('aria-hidden', 'false');
                modal.classList.add('is-visible');
                document.body.classList.add('locale-modal-open');
            };

            const closeModal = () => {
                modal.setAttribute('aria-hidden', 'true');
                modal.classList.remove('is-visible');
                document.body.classList.remove('locale-modal-open');
            };

            const firstVisit = !localStorage.getItem('appLocaleSelected');
            if (firstVisit) {
                openModal();
            }

            document.querySelectorAll('[data-open-locale-modal]').forEach((btn) => {
                btn.addEventListener('click', (event) => {
                    event.preventDefault();
                    openModal();
                });
            });

            modal.querySelectorAll('[data-locale-modal-close]').forEach((btn) => {
                btn.addEventListener('click', (event) => {
                    event.preventDefault();
                    closeModal();
                    localStorage.setItem('appLocaleSelected', '1');
                });
            });

            modal.querySelectorAll('.locale-modal__form').forEach((form) => {
                form.addEventListener('submit', () => {
                    localStorage.setItem('appLocaleSelected', '1');
                });
            });
        });
    </script>
<?php $__env->stopPush(); ?>
<?php /**PATH /home/admin/web/idrom.am/public_html/resources/views/layouts/partials/locale-modal.blade.php ENDPATH**/ ?>