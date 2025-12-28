import Headroom from "headroom.js";
import hoverintent from "./hoverintent/hoverintent";

// Entry point for the modern Civique front-end bundle.
// TODO: Port legacy functionality from civique/js/lhs.js into modular TypeScript components.

const deviceSupportsTouch = () => {
	return (
		"ontouchstart" in window ||
		navigator.maxTouchPoints > 0 ||
		// @ts-ignore
		navigator.msMaxTouchPoints > 0
	);
};

const deviceSupportsRgba = () => {
	const el = document.createElement("div");
	el.style.cssText = "background-color:rgba(0,0,0,0.5)";
	return !!el.style.backgroundColor.match(
		/^rgba\((\s*\d+\s*,){3}\s*(0?\.?\d+|1(\.0)?)\s*\)$/
	);
};

const $ = document.querySelectorAll.bind(document);

const toggleClass = (el: Element, className: string) => {
	if (!el.classList.contains(className)) {
		el.classList.add(className);
	} else {
		el.classList.remove(className);
	}
};

// on document ready
document.addEventListener("DOMContentLoaded", () => {
	document.documentElement.classList.remove("no-js");

	const tests = {
		rgba: deviceSupportsRgba,
		touch: deviceSupportsTouch,
	};

	for (const [name, test] of Object.entries(tests)) {
		if (test()) {
			document.documentElement.classList.add(name);
		} else {
			document.documentElement.classList.add(`no-${name}`);
		}
	}

	// remove heights on img;
	$("img").forEach((img) => {
		img.removeAttribute("height");
	});

	// initialize headroom.js on .headroom
	const [header] = $(".headroom");
	const headroom = new Headroom(header, {
		offset: 100,
	});
	headroom.init();

	// add has-submenu class to parents of .sub-menu
	$(".sub-menu").forEach((submenu) => {
		const parent = submenu.parentElement;
		if (parent) {
			parent.classList.add("has-submenu");
		}
	});

	// toggle hover class on touch nav menu items
	$(".mobile-menu-toggle").forEach((toggle) => {
		toggle.addEventListener("click", (e) => {
			e.preventDefault();
			let target = toggle.parentElement;

			const dataTarget = toggle.getAttribute("data-target");
			if (dataTarget) {
				target = document.querySelector(dataTarget);
			}

			if (target) {
				toggleClass(target, "active");
			}
		});
	});

	if (deviceSupportsTouch()) {
		$(".touch nav.menu>ul>li").forEach((li) => {
			li.addEventListener("click", () => {
				toggleClass(li, "hover");
			});
		});
	} else {
		$<HTMLLIElement>("nav.menu>ul>li").forEach((li) => {
			hoverintent(
				li,
				() => li.classList.add("hover"),
				() => li.classList.remove("hover")
			).options({ interval: 50 });
		});
	}

	// jQuery(".touch nav.menu>ul>li").click(function () {
	// 	const $this = jQuery(this);
	// 	if ($this.hasClass("hover")) {
	// 		$this.removeClass("hover");
	// 	} else {
	// 		$this.addClass("hover");
	// 	}
	// });
});
