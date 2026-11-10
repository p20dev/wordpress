<?php

defined('ABSPATH') || exit;

$attached = $attributes['source'] === 'attached';
$post_id = absint($block->context['postId'] ?? 0);
$ids = $attributes['imageIds'];
if ($attached) {
    $ids = $post_id ? get_posts(array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'post_parent' => $post_id,
        'post_mime_type' => 'image',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'orderby' => array('menu_order' => 'ASC', 'ID' => 'ASC'),
    )) : array();
}

$items = array();
foreach (array_unique(array_filter(array_map('absint', $ids))) as $id) {
    if (!wp_attachment_is_image($id)) {
        continue;
    }
    $image = wp_get_attachment_image($id, 'large');
    if (!$image) {
        continue;
    }
    $items[] = array(
        'image' => $image,
        'url' => $attributes['fullSize'] ? wp_get_attachment_image_url($id, 'full') : false,
        'caption' => $attributes['captions'] ? wp_get_attachment_caption($id) : '',
    );
}

if (!$items) {
    return;
}

$layout = $attributes['layout'];
$carousel = $layout === 'carousel';
$class = 'p20-gallery p20-gallery--' . $layout;
$style = sprintf('--p20-columns:%d;--p20-gap:%dpx', max(1, min(6, $attributes['columns'])), max(0, min(64, $attributes['gap'])));
if ($attributes['captions']) {
    $style .= ';--p20-caption-alignment:' . $attributes['captionAlignment'];
}
if ($carousel && $attributes['carouselEqualHeight']) {
    $class .= ' p20-gallery--equal-height';
}
if ($carousel && $attributes['carouselMaxHeight'] > 0) {
    $style .= sprintf(';--p20-carousel-max-height:%dpx', min(1200, $attributes['carouselMaxHeight']));
}
$wrapper = get_block_wrapper_attributes(array(
    'class' => $class,
    'style' => $style,
));
?>
<div <?php echo $wrapper; ?>>
    <?php if ($carousel) : ?><div class="p20-gallery-carousel-frame"><?php endif; ?>
    <?php if ($carousel && count($items) > 1) : ?>
        <div class="p20-gallery-controls" data-loop="<?php echo $attributes['carouselLoop'] ? '1' : '0'; ?>" data-autoplay="<?php echo $attributes['carouselAutoplay'] ? '1' : '0'; ?>" hidden>
            <button type="button" data-p20-step="-1" aria-label="<?php esc_attr_e('Previous image', 'guten-gallery'); ?>">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6 L9 12 L15 18"/></svg>
            </button>
            <button type="button" data-p20-step="1" aria-label="<?php esc_attr_e('Next image', 'guten-gallery'); ?>">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6 L15 12 L9 18"/></svg>
            </button>
        </div>
    <?php endif; ?>
    <ul class="p20-gallery-items" role="list" <?php if ($carousel) : ?>tabindex="0" aria-label="<?php esc_attr_e('Gallery images', 'guten-gallery'); ?>"<?php endif; ?>>
        <?php foreach ($items as $item) : ?>
            <li>
                <figure>
                    <?php if ($item['url']) : ?>
                        <a class="p20-gallery-full" href="<?php echo esc_url($item['url']); ?>" aria-label="<?php esc_attr_e('Open full-size image', 'guten-gallery'); ?>"><?php echo $item['image']; ?></a>
                    <?php else : ?>
                        <?php echo $item['image']; ?>
                    <?php endif; ?>
                    <?php if ($item['caption'] !== '') : ?>
                        <figcaption><?php echo wp_kses_post($item['caption']); ?></figcaption>
                    <?php endif; ?>
                </figure>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php if ($carousel) : ?></div><?php endif; ?>
    <?php if ($carousel && count($items) > 1) : ?>
        <div class="p20-gallery-pagination" hidden>
            <?php foreach ($items as $index => $item) : ?>
                <button type="button" data-p20-index="<?php echo (int) $index; ?>" aria-label="<?php echo esc_attr(sprintf(__('Go to image %1$d of %2$d', 'guten-gallery'), $index + 1, count($items))); ?>"></button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($attributes['fullSize']) : ?>
        <dialog class="p20-gallery-dialog" aria-label="<?php esc_attr_e('Full-size image', 'guten-gallery'); ?>">
            <button type="button" autofocus aria-label="<?php esc_attr_e('Close image', 'guten-gallery'); ?>"><span aria-hidden="true">&times;</span></button>
            <img alt="">
        </dialog>
    <?php endif; ?>
</div>
