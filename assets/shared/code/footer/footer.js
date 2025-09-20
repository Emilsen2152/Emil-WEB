import { base } from '../base/base.js';

// Last footer
fetch(`${base}assets/shared/code/footer/footer.html`)
	.then(res => res.text())
	.then(data => {
		const footer = document.querySelector(".footer");
		footer.innerHTML = data;

		footer.querySelectorAll("a[data-url]").forEach(link => {
			const url = link.dataset.url;
			link.href = /^https?:\/\//.test(url) ? url : base + url;
		});
	})
	.catch(err => console.error("Failed to load footer:", err));
