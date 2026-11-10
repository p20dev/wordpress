(function (wp) {
    const { registerBlockType } = wp.blocks;
    const { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } = wp.blockEditor;
    const { Button, PanelBody, RangeControl, SelectControl, ToggleControl } = wp.components;
    const { createElement: h, Fragment } = wp.element;
    const ServerSideRender = wp.serverSideRender.ServerSideRender || wp.serverSideRender.default || wp.serverSideRender;

    const layouts = [
        { label: 'Grid', value: 'grid' },
        { label: 'Masonry', value: 'masonry' },
        { label: 'Carousel', value: 'carousel' },
        { label: 'Mosaic', value: 'mosaic' },
    ];

    registerBlockType('p20-guten-gallery/gallery', {
        apiVersion: 3,
        title: 'Guten Gallery',
        category: 'media',
        icon: 'format-gallery',
        edit({ attributes, setAttributes, context = {} }) {
            const blockProps = useBlockProps();
            const { source, imageIds, layout, columns, gap, captions, fullSize } = attributes;
            const contextId = Number(context.postId);
            const postId = Number.isInteger(contextId) && contextId > 0 ? contextId : 0;
            const manual = source === 'manual';
            const emptyMessage = manual
                ? 'Select images from the Media Library to create a gallery.'
                : 'No images are attached to the current post. Save the post and upload images as attachments to it.';

            return h(Fragment, null,
                h(InspectorControls, null,
                    h(PanelBody, { title: 'Gallery settings' },
                        h(SelectControl, {
                            label: 'Image source',
                            value: source,
                            options: [
                                { label: 'Selected images', value: 'manual' },
                                { label: 'Attached to current post', value: 'attached' },
                            ],
                            onChange: (source) => setAttributes({ source }),
                        }),
                        h(SelectControl, {
                            label: 'Layout', value: layout, options: layouts,
                            onChange: (layout) => setAttributes({ layout }),
                        }),
                        (layout === 'grid' || layout === 'masonry') && h(RangeControl, {
                            label: 'Columns', value: columns, min: 1, max: 6,
                            onChange: (columns) => setAttributes({ columns }),
                        }),
                        h(RangeControl, {
                            label: 'Gap (px)', value: gap, min: 0, max: 64,
                            onChange: (gap) => setAttributes({ gap }),
                        }),
                        h(ToggleControl, {
                            label: 'Show captions', checked: captions,
                            onChange: (captions) => setAttributes({ captions }),
                        }),
                        captions && h(SelectControl, {
                            label: 'Caption alignment',
                            value: attributes.captionAlignment,
                            options: [
                                { label: 'Left', value: 'left' },
                                { label: 'Center', value: 'center' },
                                { label: 'Right', value: 'right' },
                            ],
                            onChange: (captionAlignment) => setAttributes({ captionAlignment }),
                        }),
                        h(ToggleControl, {
                            label: 'Open full-size images', checked: fullSize,
                            onChange: (fullSize) => setAttributes({ fullSize }),
                        }),
                        layout === 'carousel' && h(Fragment, null,
                            h(ToggleControl, {
                                label: 'Equal image heights', checked: attributes.carouselEqualHeight,
                                onChange: (carouselEqualHeight) => setAttributes({ carouselEqualHeight }),
                            }),
                            h(RangeControl, {
                                label: 'Maximum image height (px)', value: attributes.carouselMaxHeight,
                                min: 0, max: 1200, step: 10, help: 'Use 0 for no limit.',
                                onChange: (carouselMaxHeight) => setAttributes({ carouselMaxHeight }),
                            }),
                            h(ToggleControl, {
                                label: 'Autoplay', checked: attributes.carouselAutoplay,
                                onChange: (carouselAutoplay) => setAttributes({ carouselAutoplay }),
                            }),
                            h(ToggleControl, {
                                label: 'Loop from last to first', checked: attributes.carouselLoop,
                                onChange: (carouselLoop) => setAttributes({ carouselLoop }),
                            }),
                        ),
                    ),
                ),
                h('div', blockProps,
                    manual && h(MediaUploadCheck, null, h(MediaUpload, {
                        allowedTypes: ['image'],
                        multiple: true,
                        gallery: true,
                        value: imageIds,
                        onSelect: (images) => setAttributes({ imageIds: images.map(({ id }) => id) }),
                        render: ({ open }) => h(Button, {
                            variant: 'secondary', onClick: open,
                        }, imageIds.length ? 'Edit gallery images' : 'Select or upload images'),
                    })),
                    (!manual && !postId) ? h('p', null, 'No post context is available for this gallery.') :
                        manual && !imageIds.length ? h('p', null, emptyMessage) :
                            h('div', { className: 'p20-gallery-preview', inert: true },
                                h(ServerSideRender, {
                                    block: 'p20-guten-gallery/gallery',
                                    attributes,
                                    httpMethod: 'POST',
                                    urlQueryArgs: postId ? { post_id: postId } : {},
                                    EmptyResponsePlaceholder: () => h('p', null, emptyMessage),
                                }),
                            ),
                ),
            );
        },
        save: () => null,
    });
})(window.wp);
