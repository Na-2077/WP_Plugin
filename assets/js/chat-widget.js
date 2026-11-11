(function () {
	'use strict';
	var root = document.getElementById('wp-rag-assistant');
	if (!root || typeof wpRagAssistant === 'undefined') return;
	var messages = root.querySelector('.wp-rag-assistant__messages');
	var form = root.querySelector('.wp-rag-assistant__form');
	var input = root.querySelector('.wp-rag-assistant__input');
	form.addEventListener('submit', function (event) {
		event.preventDefault();
		var query = input.value.trim();
		if (!query) return;
		messages.insertAdjacentHTML('beforeend', '<p class="is-user"></p>');
		messages.lastElementChild.textContent = query;
		input.value = '';
		fetch(wpRagAssistant.endpoint, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ query: query })
		}).then(function (response) {
			return response.json().then(function (data) {
				if (!response.ok) throw new Error(data.message || wpRagAssistant.strings.error);
				return data;
			});
		}).then(function (data) {
			var answer = document.createElement('p');
			answer.className = 'is-assistant';
			answer.textContent = data.answer;
			messages.appendChild(answer);
		}).catch(function (error) {
			var message = document.createElement('p');
			message.className = 'is-error';
			message.textContent = error.message;
			messages.appendChild(message);
		});
	});
}());
