document.addEventListener('DOMContentLoaded', function() {
    // When clicking the "Load More" button
	const buttonLoadMore = document.querySelector('.post-by-date-block');
	if (buttonLoadMore) {
		buttonLoadMore.addEventListener('click', function(event) {
			const button = event.target;

			// Check if the button was clicked
			if (button.classList.contains('load-more-posts')) {
				const page = button.getAttribute('data-page');
				const pageQuantity = button.getAttribute('data-page-limit');
				const category = button.getAttribute('data-category');
				const date = button.getAttribute('data-date');
				const limit = button.getAttribute('data-limit');

				// Update the page number for the next load
				button.setAttribute('data-page', parseInt(page) + 1);

				// Create an AJAX request
				const xhr = new XMLHttpRequest();
				xhr.open('POST', load_more_posts.ajax_url, true);
				xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');

				// Define what to do when the response is ready
				xhr.onload = function() {
					if (xhr.status === 200) {
						const response = xhr.responseText;

						if (response !== 'no-more-posts') {
							// Insert the new posts before the button
							button.insertAdjacentHTML('beforebegin', response);
							button.textContent = 'Load More';
						} else {
							// No more posts, remove the button
							button.remove();
						}
					} else {
						console.error('Error loading more posts:', xhr.statusText);
					}
				};

				// Send the request data
				xhr.send(`action=load_more_posts&page=${page}&category=${category}&date=${date}&limit=${limit}`);

				// Change the button text while posts are loading
				button.textContent = 'Loading...';
			}
		});
	}
});
