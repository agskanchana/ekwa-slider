(function(wp){
	const { registerBlockType } = wp.blocks;
	const { __ } = wp.i18n;
	const { InspectorControls, InnerBlocks } = wp.blockEditor || wp.editor;
	const { PanelBody, TextControl } = wp.components;
	const NumberControl = wp.components.__experimentalNumberControl;

	registerBlockType('ekwa/slider-content', {
		title: __('Slider Content', 'ekwa-slider'),
		icon: 'layout',
		category: 'widgets',
		parent: ['ekwa/slide-item'], // Only allow inside slide-item block
		supports: {
			html: false,
			multiple: true // Allow multiple instances
		},
		attributes: {
			animationDelay: {
				type: 'number',
				default: 0
			},
			animationType: {
				type: 'string',
				default: ''
			}
		},
		edit: (props) => {
			const { attributes, setAttributes } = props;
			const { animationDelay, animationType } = attributes;

			// Use NumberControl if available, fallback to TextControl
			const NumberField = NumberControl || TextControl;

			return [
				wp.element.createElement(InspectorControls, {},
					wp.element.createElement(PanelBody, {
						title: __('Animation Settings', 'ekwa-slider'),
						initialOpen: true
					}, [
						wp.element.createElement(TextControl, {
							label: __('Animation Type', 'ekwa-slider'),
							value: animationType,
							onChange: (value) => setAttributes({ animationType: value }),
							placeholder: __('e.g. fadeIn, slideUp, bounceIn', 'ekwa-slider'),
							help: __('CSS class or animation name for this content block', 'ekwa-slider')
						}),
						wp.element.createElement(NumberField, {
							label: __('Animation Delay (ms)', 'ekwa-slider'),
							value: animationDelay,
							onChange: (value) => setAttributes({ animationDelay: parseInt(value) || 0 }),
							min: 0,
							max: 10000,
							step: 100,
							help: __('Delay before animation starts in milliseconds', 'ekwa-slider')
						})
					])
				),
				wp.element.createElement('div', {
					className: 'ekwa-slider-content-editor',
					style: {
						border: '2px dashed #0073aa',
						borderRadius: '4px',
						padding: '15px',
						margin: '10px 0',
						background: animationType ? 'rgba(0,115,170,0.05)' : 'rgba(0,0,0,0.02)'
					}
				}, [
					wp.element.createElement('div', {
						style: {
							fontSize: '12px',
							color: '#666',
							marginBottom: '10px',
							fontWeight: '500'
						}
					}, [
						__('Slider Content', 'ekwa-slider'),
						animationType && wp.element.createElement('span', {
							style: {
								marginLeft: '8px',
								padding: '2px 6px',
								background: '#0073aa',
								color: 'white',
								borderRadius: '3px',
								fontSize: '10px'
							}
						}, animationType),
						animationDelay > 0 && wp.element.createElement('span', {
							style: {
								marginLeft: '4px',
								color: '#0073aa'
							}
						}, `+${animationDelay}ms`)
					]),
					wp.element.createElement(InnerBlocks, {
						templateLock: false,
						placeholder: __('Add any blocks here (text, images, buttons, etc.)', 'ekwa-slider')
					})
				])
			];
		},
		save: () => wp.element.createElement(InnerBlocks.Content)
	});
})(window.wp);