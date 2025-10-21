(function(wp){
	const { registerBlockType } = wp.blocks;
	const { __ } = wp.i18n;
	const { InspectorControls } = wp.blockEditor || wp.editor;
	const { PanelBody, TextControl } = wp.components;
	const ServerSideRender = wp.serverSideRender || wp.components.ServerSideRender;

	registerBlockType('ekwa/slider', {
		title: __('Ekwa Slider', 'ekwa-slider'),
		description: __('Display the Ekwa Slider with all published slides', 'ekwa-slider'),
		icon: 'images-alt2',
		category: 'widgets',
		keywords: [__('slider', 'ekwa-slider'), __('carousel', 'ekwa-slider'), __('ekwa', 'ekwa-slider')],
		attributes: {
			className: {
				type: 'string',
				default: ''
			}
		},
		supports: {
			align: ['wide', 'full'],
			html: false
		},

		edit: function(props) {
			const { attributes, setAttributes } = props;
			const { className } = attributes;

			return wp.element.createElement(
				wp.element.Fragment,
				null,
				// Inspector Controls
				wp.element.createElement(
					InspectorControls,
					null,
					wp.element.createElement(
						PanelBody,
						{
							title: __('Slider Settings', 'ekwa-slider'),
							initialOpen: true
						},
						wp.element.createElement(TextControl, {
							label: __('Custom CSS Class', 'ekwa-slider'),
							value: className,
							onChange: function(value) {
								setAttributes({ className: value });
							},
							help: __('Add custom CSS classes to the slider wrapper (optional)', 'ekwa-slider')
						}),
						wp.element.createElement(
							'div',
							{
								style: {
									marginTop: '15px',
									padding: '10px',
									background: '#e7f5fe',
									borderLeft: '4px solid #00a0d2',
									borderRadius: '2px'
								}
							},
							wp.element.createElement('p', { style: { margin: 0, fontSize: '13px' } }, [
								wp.element.createElement('strong', null, __('Note:', 'ekwa-slider')),
								' ',
								__('Configure slider settings (transition style, arrows, dots, mobile banner) in ', 'ekwa-slider'),
								wp.element.createElement('strong', null, 'Dashboard → Ekwa Slider')
							])
						)
					)
				),
				// Preview
				wp.element.createElement(
					'div',
					{ className: 'ekwa-slider-block-preview' },
					wp.element.createElement(ServerSideRender, {
						block: 'ekwa/slider',
						attributes: attributes
					})
				)
			);
		},

		save: function() {
			// Dynamic block - no save output needed
			return null;
		}
	});
})(window.wp);
