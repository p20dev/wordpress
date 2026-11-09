(function (wp) {
    const { createElement, Fragment } = wp.element;
    const { InspectorControls, useBlockProps } = wp.blockEditor;
    const { PanelBody, SelectControl, ToggleControl } = wp.components;
    const ServerSideRender = wp.serverSideRender.ServerSideRender || wp.serverSideRender;

    wp.blocks.registerBlockType('p20-guten-weather/forecast', {
        apiVersion: 3,
        title: 'Guten Weather',
        category: 'widgets',
        icon: 'cloud',
        edit: function ({ attributes, setAttributes }) {
            const blockProps = useBlockProps();
            const toggles = {
                showWind: 'Show wind',
                showPrecipitation: 'Show precipitation',
                showTime: 'Show forecast time',
                showIcons: 'Show weather icons'
            };
            return createElement(
                Fragment,
                null,
                createElement(
                    InspectorControls,
                    null,
                    createElement(
                        PanelBody,
                        { title: 'Forecast display' },
                        createElement(SelectControl, {
                            label: 'Layout',
                            value: attributes.layout,
                            options: [
                                { label: 'List', value: 'list' },
                                { label: 'Table', value: 'table' },
                                { label: 'Pills', value: 'pill' }
                            ],
                            onChange: layout => setAttributes({ layout })
                        }),
                        ...Object.entries(toggles).map(([key, label]) => createElement(ToggleControl, {
                            key,
                            label,
                            checked: attributes[key],
                            onChange: checked => setAttributes({ [key]: checked })
                        }))
                    )
                ),
                createElement('div', blockProps, createElement(ServerSideRender, {
                    block: 'p20-guten-weather/forecast',
                    attributes
                }))
            );
        },
        save: function () {
            return null;
        }
    });
})(window.wp);
