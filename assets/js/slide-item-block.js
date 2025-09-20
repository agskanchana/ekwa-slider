(function(wp){
	const { registerBlockType } = wp.blocks;
	const { __ } = wp.i18n;
	const { useSelect, select, dispatch } = wp.data;
	const { MediaUpload, MediaUploadCheck, InspectorControls, InnerBlocks } = wp.blockEditor || wp.editor;
	const { PanelBody, Button, Notice } = wp.components;

	registerBlockType('ekwa/slide-item', {
		title: __('Slide Item', 'ekwa-slider'),
		icon: 'format-image',
		category: 'widgets',
		supports: { html: false, multiple: false },
		attributes: {
			desktopImageId: { type: 'number' },
			mobileImageId: { type: 'number' }
		},
		edit: (props) => {
			const { attributes, setAttributes, clientId } = props;
			const { desktopImageId, mobileImageId } = attributes;

			// Enforce only one root instance inside ekwa_slide post type.
			const postType = wp.data.select('core/editor').getCurrentPostType();
			if(postType === 'ekwa_slide') {
				const blocks = select('core/block-editor').getBlocks();
				const otherInstances = blocks.filter(b=> b.name==='ekwa/slide-item' && b.clientId!==clientId);
				if(otherInstances.length){
					return wp.element.createElement('div', { className: 'ekwa-slide-item-warning'}, __('Only one Slide Item block is allowed.', 'ekwa-slider'));
				}
			}

			function onSelectDesktop(media){
				setAttributes({ desktopImageId: media.id });
			}
			function onSelectMobile(media){
				setAttributes({ mobileImageId: media.id });
			}

			function renderImageControl(label, imageId, onSelect){
				return wp.element.createElement('div', { className: 'ekwa-image-field'}, [
					wp.element.createElement('p', {}, label),
					MediaUploadCheck ? wp.element.createElement(MediaUploadCheck, {},
						wp.element.createElement(MediaUpload, {
							onSelect,
							allowedTypes: ['image'],
							value: imageId,
							render: ({ open }) => wp.element.createElement(Button, { onClick: open, variant: imageId ? 'secondary':'primary'}, imageId ? __('Change Image','ekwa-slider'):__('Select Image','ekwa-slider'))
						})
					): null,
					imageId ? wp.element.createElement(Button, { isDestructive: true, onClick: ()=> onSelect({id: undefined}) }, __('Remove','ekwa-slider')): null
				]);
			}

			// Get media objects for alt text and preview
			const desktopMedia = desktopImageId ? wp.data.select('core').getMedia(desktopImageId) : null;
			const mobileMedia = mobileImageId ? wp.data.select('core').getMedia(mobileImageId) : null;

			// Create editor preview with background image and layered content
			const editorContent = (desktopImageId && mobileImageId) ?
				wp.element.createElement('div', { className: 'ekwa-slide-item-editor-preview' }, [
					wp.element.createElement('div', { className: 'ekwa-slide-item-editor-background' }, [
						wp.element.createElement('picture', {}, [
							wp.element.createElement('source', {
								media: '(max-width: 767px)',
								srcSet: mobileMedia?.source_url || ''
							}),
							wp.element.createElement('img', {
								src: desktopMedia?.source_url || '',
								alt: desktopMedia?.alt_text || mobileMedia?.alt_text || __('Slide image', 'ekwa-slider')
							})
						])
					]),
					wp.element.createElement('div', { className: 'ekwa-slide-item-editor-content' }, [
						wp.element.createElement('div', {
							style: {
								background: 'rgba(255,255,255,0.1)',
								padding: '10px',
								borderRadius: '4px',
								marginBottom: '10px'
							}
						}, __('Content will appear on top of the background image:', 'ekwa-slider')),
						wp.element.createElement(InnerBlocks, {
							templateLock: false,
							allowedBlocks: ['ekwa/slider-content'],
							template: [['ekwa/slider-content', {}]],
							placeholder: __('Add Slider Content blocks here', 'ekwa-slider')
						})
					])
				])
				: wp.element.createElement('div', { className: 'ekwa-slide-item-inner'}, [
					(!desktopImageId || !mobileImageId) && wp.element.createElement('div', {
						style: {
							padding: '20px',
							textAlign: 'center',
							background: '#f0f0f0',
							border: '2px dashed #ccc',
							borderRadius: '4px'
						}
					}, __('Select both desktop and mobile images to see background preview', 'ekwa-slider')),
					wp.element.createElement(InnerBlocks, {
						templateLock: false,
						allowedBlocks: ['ekwa/slider-content'],
						template: [['ekwa/slider-content', {}]],
						placeholder: __('Add Slider Content blocks here', 'ekwa-slider')
					})
				]);

			return [
				wp.element.createElement(InspectorControls, {},
					wp.element.createElement(PanelBody, { title: __('Slide Images','ekwa-slider'), initialOpen: true }, [
						renderImageControl(__('Desktop Image','ekwa-slider'), desktopImageId, onSelectDesktop),
						renderImageControl(__('Mobile Image','ekwa-slider'), mobileImageId, onSelectMobile),
						(!desktopImageId || !mobileImageId) && wp.element.createElement(Notice, { status:'warning', isDismissible:false }, __('Both desktop and mobile images are required.','ekwa-slider'))
					])
				),
				wp.element.createElement('div', { className: 'ekwa-slide-item-editor'}, [
					wp.element.createElement('fieldset', { className: 'ekwa-slide-item-fieldset' }, [
						wp.element.createElement('legend', { className: 'ekwa-slide-item-legend' }, __('Build your slider item here','ekwa-slider')),
						editorContent
					])
				])
			];
		},
		save: () => wp.element.createElement(InnerBlocks.Content)
	});
})(window.wp);
