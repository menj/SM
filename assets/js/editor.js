/**
 * Block editor registration for the SM server-rendered blocks,
 * plus the "Unlisted" toggle in the post sidebar.
 */
(function (wp) {
	'use strict';

	var el = wp.element.createElement;
	var __ = wp.i18n.__;
	var registerBlockType = wp.blocks.registerBlockType;
	var ServerSideRender = wp.serverSideRender;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var RangeControl = wp.components.RangeControl;
	var TextControl = wp.components.TextControl;
	var SelectControl = wp.components.SelectControl;
	var ToggleControl = wp.components.ToggleControl;
	var cfg = window.smEditor || {};

	var icon = el('svg', { viewBox: '0 0 24 24', width: 24, height: 24 },
		el('rect', { x: 2, y: 4, width: 20, height: 4, rx: 1, fill: '#ff0007' }),
		el('rect', { x: 2, y: 10, width: 12, height: 10, rx: 1, fill: '#7f7f7f' }),
		el('rect', { x: 16, y: 10, width: 6, height: 4, rx: 1, fill: '#fdcb30' }),
		el('rect', { x: 16, y: 16, width: 6, height: 4, rx: 1, fill: '#fdcb30' })
	);

	function serverBlock(name, title, description, controls, extraAttrs) {
		registerBlockType('sm/' + name, {
			title: title,
			description: description,
			icon: icon,
			category: 'theme',
			keywords: ['suara', 'malaysia', 'sm'],
			supports: { html: false, align: ['wide', 'full'] },
			attributes: Object.assign({ align: { type: 'string', default: 'wide' } }, extraAttrs || {}),
			edit: function (props) {
				var blockProps = useBlockProps();
				return el('div', blockProps,
					controls ? el(InspectorControls, {}, el(PanelBody, { title: __('Settings', 'sm'), initialOpen: true }, controls(props))) : null,
					el(ServerSideRender, { block: 'sm/' + name, attributes: props.attributes })
				);
			},
			save: function () { return null; }
		});
	}

	serverBlock('hero', __('Featured stories', 'sm'), __('The sticky (or latest) story with two side stories.', 'sm'), function (p) {
		return el(RangeControl, { label: __('Side stories', 'sm'), min: 0, max: 4, value: p.attributes.sideCount, onChange: function (v) { p.setAttributes({ sideCount: v }); } });
	}, { sideCount: { type: 'number', default: 2 } });

	serverBlock('sections', __('Homepage sections', 'sm'), __('One block per category chosen in Appearance → SM Theme → Homepage.', 'sm'), function (p) {
		return [
			el(RangeControl, { key: 'c', label: __('Columns', 'sm'), min: 1, max: 4, value: p.attributes.columns, onChange: function (v) { p.setAttributes({ columns: v }); } }),
			el(RangeControl, { key: 'p', label: __('Stories per section (0 = use setting)', 'sm'), min: 0, max: 6, value: p.attributes.perSection, onChange: function (v) { p.setAttributes({ perSection: v }); } })
		];
	}, { columns: { type: 'number', default: 3 }, perSection: { type: 'number', default: 0 } });

	serverBlock('related', __('You may be interested', 'sm'), __('Related stories from the same category.', 'sm'), function (p) {
		return [
			el(TextControl, { key: 't', label: __('Title', 'sm'), value: p.attributes.title, onChange: function (v) { p.setAttributes({ title: v }); } }),
			el(RangeControl, { key: 'c', label: __('Stories', 'sm'), min: 1, max: 6, value: p.attributes.count, onChange: function (v) { p.setAttributes({ count: v }); } })
		];
	}, { title: { type: 'string', default: '' }, count: { type: 'number', default: 3 } });

	serverBlock('newsletter', __('Newsletter panel', 'sm'), __('Rose sign-up panel. Subscribers are stored under Appearance → Subscribers.', 'sm'), function (p) {
		return el(TextControl, { label: __('Heading (leave empty for the setting)', 'sm'), value: p.attributes.heading, onChange: function (v) { p.setAttributes({ heading: v }); } });
	}, { heading: { type: 'string', default: '' } });

	serverBlock('contact-form', __('Contact form', 'sm'), __('Form fields are configured under Appearance → SM Theme → Contact form.', 'sm'), null, {});
	serverBlock('donate-form', __('Donate form', 'sm'), __('Amount, type and frequency chooser that hands off to your checkout URL.', 'sm'), null, {});

	serverBlock('social-icons', __('Social icons', 'sm'), __('Icons for the profiles set under Appearance → SM Theme → Social.', 'sm'), function (p) {
		var styles = Object.keys(cfg.socialStyles || {}).map(function (k) { return { value: k, label: cfg.socialStyles[k] }; });
		return [
			el(SelectControl, { key: 's', label: __('Style', 'sm'), value: p.attributes.style, options: [{ value: '', label: __('Use setting', 'sm') }].concat(styles), onChange: function (v) { p.setAttributes({ style: v }); } }),
			el(RangeControl, { key: 'z', label: __('Size (0 = use setting)', 'sm'), min: 0, max: 64, value: p.attributes.size, onChange: function (v) { p.setAttributes({ size: v }); } }),
			el(TextControl, { key: 'l', label: __('Label', 'sm'), value: p.attributes.label, onChange: function (v) { p.setAttributes({ label: v }); } })
		];
	}, { style: { type: 'string', default: '' }, size: { type: 'number', default: 0 }, label: { type: 'string', default: '' } });

	serverBlock('section-heading', __('Section heading', 'sm'), __('Title with the red rule and a "View All" link.', 'sm'), function (p) {
		return [
			el(TextControl, { key: 't', label: __('Title', 'sm'), value: p.attributes.title, onChange: function (v) { p.setAttributes({ title: v }); } }),
			el(TextControl, { key: 'l', label: __('Link URL', 'sm'), value: p.attributes.link, onChange: function (v) { p.setAttributes({ link: v }); } }),
			el(RangeControl, { key: 'h', label: __('Heading level', 'sm'), min: 1, max: 6, value: p.attributes.level, onChange: function (v) { p.setAttributes({ level: v }); } })
		];
	}, { title: { type: 'string', default: '' }, link: { type: 'string', default: '' }, level: { type: 'number', default: 2 } });

	var countTitle = function (p) {
		return [
			el(TextControl, { key: 't', label: __('Title', 'sm'), value: p.attributes.title, onChange: function (v) { p.setAttributes({ title: v }); } }),
			el(RangeControl, { key: 'c', label: __('Items', 'sm'), min: 1, max: 10, value: p.attributes.count, onChange: function (v) { p.setAttributes({ count: v }); } })
		];
	};
	var countAttrs = { title: { type: 'string', default: '' }, count: { type: 'number', default: 5 } };
	serverBlock('editors-picks', __('Editor’s Picks', 'sm'), __('Posts tagged “editors-pick”, topped up with the latest stories.', 'sm'), countTitle, countAttrs);
	serverBlock('active-topics', __('Active Topics', 'sm'), __('Categories with the most stories.', 'sm'), countTitle, countAttrs);
	serverBlock('share', __('Share buttons', 'sm'), __('WhatsApp, Facebook, X and copy-link buttons for the current article.', 'sm'), null, {});
	serverBlock('reading-time', __('Reading time', 'sm'), __('Estimated minutes to read the current article.', 'sm'), null, {});

	/* Unlisted toggle in the document sidebar */
	if (wp.plugins && wp.editPost && wp.data && cfg.unlistMeta) {
		var PluginPostStatusInfo = wp.editPost.PluginPostStatusInfo || (wp.editor && wp.editor.PluginPostStatusInfo);
		if (PluginPostStatusInfo) {
			wp.plugins.registerPlugin('sm-unlist', {
				render: function () {
					var meta = wp.data.useSelect(function (select) { return select('core/editor').getEditedPostAttribute('meta') || {}; }, []);
					var editPost = wp.data.useDispatch('core/editor').editPost;
					return el(PluginPostStatusInfo, { className: 'sm-unlist-toggle' },
						el(ToggleControl, {
							label: __('Unlisted', 'sm'),
							help: __('Hidden from lists, search and feeds. Reachable by direct link only.', 'sm'),
							checked: !!meta[cfg.unlistMeta],
							onChange: function (v) { var m = {}; m[cfg.unlistMeta] = !!v; editPost({ meta: m }); }
						})
					);
				}
			});
		}
	}
})(window.wp);
