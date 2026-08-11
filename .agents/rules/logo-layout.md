---
description: Constraints for modifying the ApplicationLogo component.
---

# ApplicationLogo Positioning Lock

The `ApplicationLogo.vue` component has been meticulously tuned by the user to achieve a perfect, premium overlap between the "INEA" (sans) and "Scents" (script) texts. 

When working on or around `ApplicationLogo.vue`, **DO NOT alter the following structural classes unless explicitly requested by the user**:

1. **Flex Wrapper**: It must use `inline-flex items-baseline justify-center` so the browser naturally calculates the combined bounding box.
2. **Negative Margin Overlap**: The "Scents" span uses `-ml-[0.95em] sm:-ml-[0.85em]` to achieve the exact "kiss" overlap with the "A" in "INEA". 
3. **Vertical Offset**: The "Scents" span uses `translate-y-[65%]` so the top loop of the 'S' sits perfectly under the crossbar of the 'A'.
4. **Knockout Stroke**: The "Scents" span uses `[-webkit-text-stroke:4px_#fdf4f5] dark:[-webkit-text-stroke:4px_#151012] [paint-order:stroke_fill]` to create a seamless cut-out effect through the "A". Do not remove this.
