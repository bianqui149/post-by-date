/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType } from '@wordpress/blocks';

import { SelectControl } from '@wordpress/components';
import { __experimentalNumberControl as NumberControl } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useBlockProps } from '@wordpress/block-editor';
import { useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './style.scss';

/**
 * Internal dependencies
 */
//import Edit from './edit';
//import metadata from './block.json';

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */


registerBlockType('create-block/post-by-date', {
    attributes: {
        category: {
            type: 'string',
            default: '',
        },
        date: {
            type: 'string',
            default: '',
        },
        limit: {
            type: 'number',
            default: '',
        },
    },

    edit: (props) => {
        const { attributes, setAttributes } = props;
        const { category, date, limit } = attributes;

        const blockProps = useBlockProps();

        // Fetch categories using getEntityRecords
        const categoryOptions = useSelect((select) => {
            return select('core').getEntityRecords('taxonomy', 'category', { hide_empty: false }) || [];
        }, []);

        // Fetch default values from the options page
        useEffect(() => {
            const fetchDefaultOptions = async () => {
                try {
                    const defaultOptions = await apiFetch({ path: '/custom/v1/settings' });
                    console.log(defaultOptions);

                    // Update only if current attributes are empty
                    setAttributes({
                        category: category || defaultOptions.category_post,
                        date: date || defaultOptions.category_date,
                        limit: limit || defaultOptions.category_limit, // Use default of 5 if not set
                    });
                } catch (error) {
                    console.error('Error fetching default options:', error);
                }
            };

            fetchDefaultOptions();
        }, []);

        return (
			<div {...blockProps}>
				{/* First row with only the SelectControl */}
				<div className="row centered">
					<SelectControl
						label="Select Category"
						value={category}
						options={categoryOptions.map(cat => ({
							label: cat.name,
							value: cat.id,
						}))}
						onChange={(newCategory) => setAttributes({ category: newCategory })}
					/>
				</div>

				{/* Second row with Date and NumberControl centered */}
				<div className="row centered">
					<div className="input-group">
						<label htmlFor="post-date">Date</label>
						<input
							type="date"
							id="post-date"
							value={date}
							onChange={(e) => setAttributes({ date: e.target.value })}
						/>
					</div>

					<div className="input-group">
						<NumberControl
							label="Limit"
							value={limit}
							onChange={(newLimit) => setAttributes({ limit: parseInt(newLimit, 10) })}
							min={1}
							max={100}
						/>
					</div>
				</div>
			</div>
		);

    },

    save: () => {
        return null; // Use server-side rendering
    },
});
