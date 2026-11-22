/**
 * Planty — interactions.
 * 1. Menu mobile
 * 2. Apparition des blocs au défilement
 * 3. Commande : boutons + / − et récapitulatif
 */
(() => {
	const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* 1. Menu mobile ------------------------------------------------------- */
	const toggle = document.querySelector('.nav-toggle');
	const nav = document.getElementById('nav-principal');
	if (toggle && nav) {
		const setOpen = (open) => {
			toggle.setAttribute('aria-expanded', String(open));
			nav.classList.toggle('is-open', open);
		};
		toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' && nav.classList.contains('is-open')) {
				setOpen(false);
				toggle.focus();
			}
		});
		window.addEventListener('resize', () => {
			if (window.innerWidth > 760) setOpen(false);
		});
	}

	/* 2. Apparition au défilement ------------------------------------------ */
	const reveals = document.querySelectorAll('[data-reveal]');
	if (reduceMotion || !('IntersectionObserver' in window)) {
		reveals.forEach((el) => el.classList.add('is-visible'));
	} else {
		const io = new IntersectionObserver(
			(entries) => {
				entries.forEach((entry) => {
					if (entry.isIntersecting) {
						entry.target.classList.add('is-visible');
						io.unobserve(entry.target);
					}
				});
			},
			{ rootMargin: '0px 0px -10% 0px', threshold: 0.1 }
		);
		reveals.forEach((el) => io.observe(el));
	}

	/* Mode démo (export statique) : le bouton garde son style, mais rien n'est envoyé.
	   Le message n'apparaît qu'au clic. -------------------------------------- */
	document.querySelectorAll('form[data-demo]').forEach((form) => {
		const notice = form.querySelector('[data-demo-notice]');
		if (notice) notice.hidden = true;
		form.addEventListener('submit', (e) => {
			e.preventDefault();
			if (notice) {
				notice.hidden = false;
				notice.scrollIntoView({ block: 'nearest', behavior: reduceMotion ? 'auto' : 'smooth' });
			}
		});
	});

	/* 3. Commande : boutons + / − et récapitulatif en direct -------------- */
	const order = document.querySelector('.form--order');
	if (order) {
		const inputs = [...order.querySelectorAll('.order-taste__qty')];
		const summary = order.querySelector('.order__summary');
		const euro = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });
		const clamp = (n) => Math.min(99, Math.max(0, Number.isFinite(n) ? Math.round(n) : 0));

		const render = () => {
			let count = 0;
			let amount = 0;
			let priced = true;
			const parts = [];
			inputs.forEach((input) => {
				const n = clamp(parseInt(input.value, 10));
				if (n > 0) {
					count += n;
					if (input.dataset.price === undefined) priced = false;
					else amount += n * parseFloat(input.dataset.price);
					parts.push({ n, name: input.dataset.name, color: getComputedStyle(input.closest('.order-taste')).getPropertyValue('--gout').trim() });
				}
			});

			summary.replaceChildren();
			if (!count) {
				summary.textContent = 'Aucune canette pour l’instant.';
				return;
			}
			const total = document.createElement('strong');
			total.textContent = `${count} canette${count > 1 ? 's' : ''}`;
			summary.append(total);
			const list = document.createElement('span');
			list.className = 'order__chips';
			parts.forEach((p) => {
				const chip = document.createElement('span');
				chip.className = 'order__chip';
				if (p.color) chip.style.setProperty('--gout', p.color);
				chip.textContent = `${p.n} ${p.name}`;
				list.append(chip);
			});
			summary.append(list);
			if (priced) {
				const price = document.createElement('strong');
				price.className = 'order__total';
				price.textContent = euro.format(amount);
				summary.append(price);
			}
		};

		order.querySelectorAll('.qty__btn').forEach((btn) => {
			btn.hidden = false;
			btn.addEventListener('click', () => {
				const input = document.getElementById(btn.getAttribute('aria-controls'));
				input.value = clamp(parseInt(input.value, 10) + Number(btn.dataset.step));
				render();
			});
		});
		inputs.forEach((input) => {
			input.addEventListener('input', render);
			input.addEventListener('change', () => {
				input.value = clamp(parseInt(input.value, 10));
				render();
			});
		});
		// Goût choisi depuis l'accueil (?gout=ID) : 1 canette au départ.
		// Le PHP le fait déjà ; ceci sert à la démo statique, où le PHP ne tourne pas.
		const picked = new URLSearchParams(window.location.search).get('gout');
		const pickedInput = picked && order.querySelector(`[name="quantite[${CSS.escape(picked)}]"]`);
		if (pickedInput && inputs.every((input) => !parseInt(input.value, 10))) {
			pickedInput.value = 1;
		}

		summary.hidden = false;
		render();
	}
})();
