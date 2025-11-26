import { base } from '../base/base.js';

// Last sidebar
fetch(`${base}assets/shared/code/sidebar/sidebar.html`)
	.then(res => res.text())
	.then(data => {
		const sidebar = document.querySelector(".sidebar");
		sidebar.innerHTML = data;

		sidebar.querySelectorAll("a[data-url]").forEach(link => {
			const url = link.dataset.url;
			link.href = /^https?:\/\//.test(url) ? url : base + url;
		});
	})
	.catch(err => console.error("Failed to load sidebar:", err));
