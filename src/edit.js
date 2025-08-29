import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, PanelColorSettings } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, SelectControl, RangeControl } from '@wordpress/components';
import '../editor.css';

export default function Edit({ attributes, setAttributes }) {
    const {
        postsPerPage,
        order,
        orderby,
        ratingOrder,
        recipeCategory,
        cuisineType,
        dietaryRestriction,
        showTitle,
        showExcerpt,
        useExcerpt,
        showPrepTime,
        showDifficulty,
        showRatings,
        colorText,
        colorBackground,
        spacing,
        borderRadius,
        slidesPerView,
        delay,
        loop,
        autoplay,
        showArrows,
        showDots,
        breakpoints,
    } = attributes;

    const setBreakpoint = (width, slides) => {
        const newBreakpoints = { ...breakpoints };
        if (slides) {
            newBreakpoints[width] = { slidesPerView: parseInt(slides, 10) };
        } else {
            delete newBreakpoints[width];
        }
        setAttributes({ breakpoints: newBreakpoints });
    };

    const parseCsv = (value) => value.split(',').map(s => s.trim()).filter(Boolean);

    return (
        <div {...useBlockProps()}>
            <InspectorControls>
                <PanelBody title={__('Query', 'recipe-slider')}>
                    <RangeControl
                        label={__('Number of recipes', 'recipe-slider')}
                        value={postsPerPage}
                        onChange={(val) => setAttributes({ postsPerPage: val })}
                        min={1}
                        max={10}
                    />
                    <SelectControl
                        label={__('Order', 'recipe-slider')}
                        value={order}
                        options={[
                            { label: 'DESC', value: 'DESC' },
                            { label: 'ASC', value: 'ASC' },
                        ]}
                        onChange={(val) => setAttributes({ order: val })}
                    />
                    <SelectControl
                        label={__('Sort by', 'recipe-slider')}
                        value={orderby}
                        options={[
                            { label: __('Newest', 'recipe-slider'), value: 'date' },
                            { label: __('Alphabetical', 'recipe-slider'), value: 'title' },
                            { label: __('Most Popular (random demo)', 'recipe-slider'), value: 'rand' },
                        ]}
                        onChange={(val) => setAttributes({ orderby: val })}
                    />
                    <SelectControl
                        label={__('Rating Order', 'recipe-slider')}
                        value={ratingOrder || 'none'}
                        options={[
                            { label: __('None', 'recipe-slider'), value: 'none' },
                            { label: __('Highest First', 'recipe-slider'), value: 'desc' },
                            { label: __('Lowest First', 'recipe-slider'), value: 'asc' },
                        ]}
                        onChange={(val) => setAttributes({ ratingOrder: val })}
                    />
                    <TextControl
                        label={__('Recipe Categories (slugs, comma-separated)', 'recipe-slider')}
                        value={recipeCategory?.join(', ') || ''}
                        onChange={(val) => setAttributes({ recipeCategory: parseCsv(val) })}
                        help={__('Use slugs for now. Leave empty for all.', 'recipe-slider')}
                    />
                    <TextControl
                        label={__('Cuisine Types (slugs, comma-separated)', 'recipe-slider')}
                        value={cuisineType?.join(', ') || ''}
                        onChange={(val) => setAttributes({ cuisineType: parseCsv(val) })}
                    />
                    <TextControl
                        label={__('Dietary Restrictions (slugs, comma-separated)', 'recipe-slider')}
                        value={dietaryRestriction?.join(', ') || ''}
                        onChange={(val) => setAttributes({ dietaryRestriction: parseCsv(val) })}
                    />
                    <ToggleControl
                        label={__('Show Title', 'recipe-slider')}
                        checked={showTitle}
                        onChange={(val) => setAttributes({ showTitle: val })}
                    />
                    <ToggleControl
                        label={__('Show Excerpt', 'recipe-slider')}
                        checked={showExcerpt}
                        onChange={(val) => setAttributes({ showExcerpt: val })}
                    />
                    <ToggleControl
                        label={__('Use excerpt (off trims content)', 'recipe-slider')}
                        checked={useExcerpt}
                        onChange={(val) => setAttributes({ useExcerpt: val })}
                    />
                    <ToggleControl
                        label={__('Show Preparation Time', 'recipe-slider')}
                        checked={showPrepTime}
                        onChange={(val) => setAttributes({ showPrepTime: val })}
                    />
                    <ToggleControl
                        label={__('Show Difficulty', 'recipe-slider')}
                        checked={showDifficulty}
                        onChange={(val) => setAttributes({ showDifficulty: val })}
                    />
                    <ToggleControl
                        label={__('Show Ratings (placeholder)', 'recipe-slider')}
                        checked={showRatings}
                        onChange={(val) => setAttributes({ showRatings: val })}
                    />
                </PanelBody>
                <PanelBody title={__('Slider Settings', 'recipe-slider')}>
                    <TextControl
                        label={__('Slides Per View', 'recipe-slider')}
                        type="number"
                        value={slidesPerView}
                        onChange={(val) => setAttributes({ slidesPerView: parseInt(val, 10) })}
                        min="1"
                    />
                    <TextControl
                        label={__('Delay', 'recipe-slider')}
                        type="number"
                        value={delay}
                        onChange={(val) => setAttributes({ delay: parseInt(val, 10) })}
                        min="1"
                    />
                    <ToggleControl
                        label={__('Loop', 'recipe-slider')}
                        checked={loop}
                        onChange={(val) => setAttributes({ loop: val })}
                    />
                    <ToggleControl
                        label={__('Autoplay', 'recipe-slider')}
                        checked={autoplay}
                        onChange={(val) => setAttributes({ autoplay: val })}
                    />
                    <ToggleControl
                        label={__('Show Arrows', 'recipe-slider')}
                        checked={showArrows}
                        onChange={(val) => setAttributes({ showArrows: val })}
                    />
                    <ToggleControl
                        label={__('Show Dots', 'recipe-slider')}
                        checked={showDots}
                        onChange={(val) => setAttributes({ showDots: val })}
                    />
                </PanelBody>
                <PanelBody title={__('Responsive Settings', 'recipe-slider')} initialOpen={false}>
                    <p>{__('Leave blank to use the default Slides Per View.', 'recipe-slider')}</p>
                    <TextControl
                        label={__('Desktop (1024px)', 'recipe-slider')}
                        type="number"
                        value={breakpoints?.[1024]?.slidesPerView || ''}
                        onChange={(val) => setBreakpoint(1024, val)}
                        min="1"
                    />
                    <TextControl
                        label={__('Tablet (768px)', 'recipe-slider')}
                        type="number"
                        value={breakpoints?.[768]?.slidesPerView || ''}
                        onChange={(val) => setBreakpoint(768, val)}
                        min="1"
                    />
                    <TextControl
                        label={__('Mobile (320px)', 'recipe-slider')}
                        type="number"
                        value={breakpoints?.[320]?.slidesPerView || ''}
                        onChange={(val) => setBreakpoint(320, val)}
                        min="1"
                    />
                </PanelBody>
                <PanelColorSettings
                    title={__('Colors', 'recipe-slider')}
                    colorSettings={[
                        {
                            value: colorText,
                            onChange: (v) => setAttributes({ colorText: v || '' }),
                            label: __('Text', 'recipe-slider'),
                        },
                        {
                            value: colorBackground,
                            onChange: (v) => setAttributes({ colorBackground: v || '' }),
                            label: __('Background', 'recipe-slider'),
                        },
                    ]}
                />
                <PanelBody title={__('Spacing & Radius', 'recipe-slider')} initialOpen={false}>
                    <RangeControl
                        label={__('Card Spacing (px)', 'recipe-slider')}
                        value={spacing}
                        onChange={(v) => setAttributes({ spacing: v })}
                        min={0}
                        max={48}
                    />
                    <RangeControl
                        label={__('Border Radius (px)', 'recipe-slider')}
                        value={borderRadius}
                        onChange={(v) => setAttributes({ borderRadius: v })}
                        min={0}
                        max={32}
                    />
                </PanelBody>
            </InspectorControls>
            <div className="testimonial-editor-item">
                <p>{__('Recipes will be fetched dynamically on the front-end based on your query settings.', 'recipe-slider')}</p>
            </div>
        </div>
    );
}