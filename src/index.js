/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType } from '@wordpress/blocks';

import { SelectControl } from '@wordpress/components';
import { __experimentalNumberControl as NumberControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
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

registerBlockType( 'create-block/post-by-date', {
	attributes: {
		category: { type: 'string', default: '' },
		date: { type: 'string', default: '' },
		limit: { type: 'number', default: '' },
	},

	edit: ( props ) => {
		const { attributes, setAttributes } = props;
		const { category, date, limit } = attributes;
		const blockProps = useBlockProps();

		// Fetch categories using getEntityRecords
		const categoryOptions = useSelect( ( select ) => {
			return (
				select( 'core' ).getEntityRecords( 'taxonomy', 'category', {
					hide_empty: false,
				} ) || []
			);
		}, [] );

		// Fetch default values from the options page
		useEffect( () => {
			const fetchDefaultOptions = async () => {
				try {
					const defaultOptions = await apiFetch( {
						path: '/custom/v1/settings',
					} );
					setAttributes( {
						category: category || defaultOptions.category_post,
						date: date || defaultOptions.category_date,
						limit: limit || defaultOptions.category_limit,
					} );
				} catch ( error ) {
					console.error( 'Error fetching default options:', error );
				}
			};

			fetchDefaultOptions();
		}, [] );

		// Fetch posts
		const posts = useSelect(
			( select ) => {
				const query = {
					per_page: limit,
					categories: category,
					before: date ? new Date( date ).toISOString() : undefined,
				};
				return (
					select( 'core' ).getEntityRecords(
						'postType',
						'post',
						query
					) || []
				);
			},
			[ category, date, limit ]
		);

		// Function to remove HTML tags
		function stripTags( html ) {
			const div = document.createElement( 'div' );
			div.innerHTML = html;
			return div.textContent || div.innerText || '';
		}

		return (
			<div { ...blockProps }>
				<SelectControl
					label="Select Category"
					value={ category }
					options={ [
						{ label: 'Select Option', value: '' },
						...categoryOptions.map( ( cat ) => ( {
							label: cat.name,
							value: cat.id,
						} ) ),
					] }
					onChange={ ( newCategory ) =>
						setAttributes( { category: newCategory } )
					}
				/>

				<label htmlFor="post-date">Date</label>
				<input
					type="date"
					id="post-date"
					value={ date }
					onChange={ ( e ) =>
						setAttributes( { date: e.target.value } )
					}
				/>

				<NumberControl
					label="Limit"
					value={ limit ? limit : 5}
					onChange={ ( newLimit ) =>
						setAttributes( { limit: parseInt( newLimit, 10 ) } )
					}
					min={ 1 }
					max={ 100 }
				/>

				{ /* Render posts in the editor */ }

				{ posts.length > 0 ? (
					<div className="post-by-date-block">
						{ posts.map( ( post ) => (
							<div className="post-item" key={ post.id }>
								<h3>{ post.title.rendered }</h3>
								<p>{ stripTags( post.excerpt.rendered ) }</p>
								<p>
									<small>
										Published on:{ ' ' }
										{ post.date.substring( 0, 10 ) }
									</small>
								</p>
							</div>
						) ) }
					</div>
				) : (
					<p>No posts found.</p>
				) }
			</div>
		);
	},

	save: () => {
		return null; // Server-side rendering
	},
} );
