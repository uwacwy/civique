export interface HoverIntentOptions {
	sensitivity: number;
	interval: number;
	timeout: number;
	handleFocus: boolean;
}

export interface HoverIntentController {
	options(opt: Partial<HoverIntentOptions>): HoverIntentController;
	remove(): void;
}

type HoverIntentCallback = (this: Element, event: Event) => void;

type TimeoutHandle = ReturnType<typeof setTimeout> | undefined;

export default function hoverintent<HostElement extends HTMLElement>(
	el: HostElement | null,
	onOver: HoverIntentCallback,
	onOut: HoverIntentCallback
): HoverIntentController {
	let x = 0;
	let y = 0;
	let pX = 0;
	let pY = 0;
	let mouseOver = false;
	let focused = false;
	let state = 0;
	let timer: TimeoutHandle;

	function hasElement<TElement extends Element>(
		value: TElement | null
	): value is TElement {
		return value !== null;
	}

	function clearExistingTimer() {
		if (timer !== undefined) {
			clearTimeout(timer);
			timer = undefined;
		}
	}

	let options: HoverIntentOptions = {
		sensitivity: 7,
		interval: 100,
		timeout: 0,
		handleFocus: false,
	};

	const controller: HoverIntentController = {
		options(opt: Partial<HoverIntentOptions>) {
			const focusOptionChanged = opt.handleFocus !== options.handleFocus;
			options = { ...options, ...opt };
			if (focusOptionChanged) {
				if (options.handleFocus) {
					addFocus();
				} else {
					removeFocus();
				}
			}
			return controller;
		},
		remove() {
			if (!hasElement(el)) {
				return;
			}
			el.removeEventListener("mouseover", dispatchOver);
			el.removeEventListener("mouseout", dispatchOut);
			el.removeEventListener("mousemove", tracker);
			removeFocus();
			clearExistingTimer();
			state = 0;
			mouseOver = false;
			focused = false;
		},
	};

	const delay = (element: Element, event: MouseEvent) => {
		clearExistingTimer();
		state = 0;
		return focused ? undefined : onOut.call(element, event);
	};

	function tracker(event: MouseEvent) {
		x = event.clientX;
		y = event.clientY;
	}

	function compare(element: Element, event: MouseEvent) {
		clearExistingTimer();
		if (Math.abs(pX - x) + Math.abs(pY - y) < options.sensitivity) {
			state = 1;
			return focused ? undefined : onOver.call(element, event);
		}
		pX = x;
		pY = y;
		timer = setTimeout(() => {
			compare(element, event);
		}, options.interval);
		return undefined;
	}

	const dispatchOver = (event: MouseEvent) => {
		if (!hasElement(el)) {
			return controller;
		}
		mouseOver = true;
		clearExistingTimer();
		const element = el;
		element.removeEventListener("mousemove", tracker);
		if (state !== 1) {
			pX = event.clientX;
			pY = event.clientY;
			element.addEventListener("mousemove", tracker);
			timer = setTimeout(() => {
				compare(element, event);
			}, options.interval);
		}
		return controller;
	};

	const dispatchOut = (event: MouseEvent) => {
		if (!hasElement(el)) {
			return controller;
		}
		mouseOver = false;
		clearExistingTimer();
		const element = el;
		element.removeEventListener("mousemove", tracker);
		if (state === 1) {
			timer = setTimeout(() => {
				delay(element, event);
			}, options.timeout);
		}
		return controller;
	};

	const dispatchFocus = (event: FocusEvent) => {
		if (mouseOver || !hasElement(el)) {
			return;
		}
		focused = true;
		onOver.call(el, event);
	};

	const dispatchBlur = (event: FocusEvent) => {
		if (mouseOver || !focused || !hasElement(el)) {
			return;
		}
		focused = false;
		onOut.call(el, event);
	};

	const addFocus = () => {
		if (!hasElement(el)) {
			return;
		}
		el.addEventListener("focus", dispatchFocus);
		el.addEventListener("blur", dispatchBlur);
	};

	const removeFocus = () => {
		if (!hasElement(el)) {
			return;
		}
		el.removeEventListener("focus", dispatchFocus);
		el.removeEventListener("blur", dispatchBlur);
	};

	if (hasElement(el)) {
		el.addEventListener("mouseover", dispatchOver);
		el.addEventListener("mouseout", dispatchOut);
		if (options.handleFocus) {
			addFocus();
		}
	}

	return controller;
}
