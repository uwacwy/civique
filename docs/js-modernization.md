# Civique JS Modernization Notes

## Legacy bundle audit (`civique/js/lhs.js`)

- Bundles a bespoke Modernizr 2.8.2 build plus HTML5 Shiv directly in the file.
- Includes Headroom 0.7.0 with the jQuery integration plugin to attach `headroom()` helpers.
- Copies Brian Cherne's `hoverIntent` jQuery plugin for desktop menu rollover timing.
- Relies on jQuery for DOM readiness, menu toggles, hover bindings, and selecting `data-target` panels.
- Applies global tweaks (e.g., removing fixed image heights) and toggles CSS classes to drive the mobile menu.

## Target npm sources

- [`headroom.js`](https://www.npmjs.com/package/headroom.js) → provides the vanilla API we can wrap in a small helper instead of using the legacy plugin.
- [`hoverintent`](https://www.npmjs.com/package/hoverintent) → modern, dependency-free replacement for the jQuery plugin.
- [`modernizr`](https://www.npmjs.com/package/modernizr) → optional if we still need runtime feature flags; alternatively we can drop the dependency after auditing template usage.

## TypeScript migration outline

1. Introduce DOM-focused utilities (e.g., `queryAll`, `delegate`) in `src/scripts/lib/` to avoid reimplementing helpers in each component.
2. Port the mobile menu toggle into `src/scripts/components/mobile-menu-toggle.ts`, exporting an initializer consumed by `index.ts`.
3. Implement a `HeadroomController` module that instantiates Headroom for `.headroom` elements using the npm package.
4. Replace the jQuery hover/blur menu behaviour with a new component that uses `hoverintent` and a pointer-capable media query check.
5. Remove the inline Modernizr build after confirming no templates rely on its class toggles; keep a lean feature detection helper if required.
6. Once new modules cover the legacy behaviours, retire `civique/js/lhs.js` and adjust `functions.php` to enqueue the bundled output from `civique/js/dist/lhs.js`.

## Outstanding questions

- Do any templates still rely on the Modernizr-generated HTML classes (e.g., `.no-touch`)? If so, reproduce equivalent detection in a dedicated TypeScript helper.
- Can we replace the current Zepto fallback with native event delegation entirely, or is there any third-party plugin expecting a jQuery interface?
- Are there analytics or external scripts enqueued elsewhere that depend on the global `jQuery` object? Confirm before removing it from the theme entirely.
