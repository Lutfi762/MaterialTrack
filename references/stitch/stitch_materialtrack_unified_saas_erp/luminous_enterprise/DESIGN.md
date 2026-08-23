---
name: Luminous Enterprise
colors:
  surface: '#111319'
  surface-dim: '#111319'
  surface-bright: '#373940'
  surface-container-lowest: '#0c0e14'
  surface-container-low: '#191b22'
  surface-container: '#1e1f26'
  surface-container-high: '#282a30'
  surface-container-highest: '#33343b'
  on-surface: '#e2e2eb'
  on-surface-variant: '#c7c4d7'
  inverse-surface: '#e2e2eb'
  inverse-on-surface: '#2e3037'
  outline: '#908fa0'
  outline-variant: '#464554'
  surface-tint: '#c0c1ff'
  primary: '#c0c1ff'
  on-primary: '#1000a9'
  primary-container: '#8083ff'
  on-primary-container: '#0d0096'
  inverse-primary: '#494bd6'
  secondary: '#d2bbff'
  on-secondary: '#3f008e'
  secondary-container: '#6001d1'
  on-secondary-container: '#c9aeff'
  tertiary: '#ddb7ff'
  on-tertiary: '#490080'
  tertiary-container: '#b76dff'
  on-tertiary-container: '#400071'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#e1e0ff'
  primary-fixed-dim: '#c0c1ff'
  on-primary-fixed: '#07006c'
  on-primary-fixed-variant: '#2f2ebe'
  secondary-fixed: '#eaddff'
  secondary-fixed-dim: '#d2bbff'
  on-secondary-fixed: '#25005a'
  on-secondary-fixed-variant: '#5a00c6'
  tertiary-fixed: '#f0dbff'
  tertiary-fixed-dim: '#ddb7ff'
  on-tertiary-fixed: '#2c0051'
  on-tertiary-fixed-variant: '#6900b3'
  background: '#111319'
  on-background: '#e2e2eb'
  surface-variant: '#33343b'
typography:
  display-lg:
    fontFamily: Geist
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Geist
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Geist
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  title-lg:
    fontFamily: Geist
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Geist
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Geist
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-md:
    fontFamily: Geist
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
    letterSpacing: 0.05em
  wordmark-material:
    fontFamily: Geist
    fontSize: 20px
    fontWeight: '800'
    lineHeight: 24px
  wordmark-track:
    fontFamily: Geist
    fontSize: 20px
    fontWeight: '400'
    lineHeight: 24px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  unit: 8px
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
  sidebar-width: 260px
  sidebar-collapsed: 72px
  header-height: 64px
---

## Brand & Style

The design system is engineered for high-performance ERP environments where clarity meets premium aesthetics. The brand personality is **authoritative, sophisticated, and technologically advanced**, designed to evoke a sense of precision and "intelligent control" over complex logistics data.

The visual style is a fusion of **Corporate Modern** and **Glassmorphism**. It utilizes a deep, immersive dark mode foundation to reduce eye strain during long analytical sessions, punctuated by vibrant linear gradients that signify movement and live data tracking. Tactile depth is achieved through subtle "inner-glow" borders and multi-layered translucency, creating a UI that feels like a futuristic command center rather than a static spreadsheet.

## Colors

The palette is anchored by a deep charcoal-navy (`#0F1117`) for the primary background, providing maximum contrast for the vivid accent system. 

- **Primary & Gradients:** The core identity is expressed through a blue-to-purple spectral gradient. This gradient should be used for primary actions, active state highlights, and progress indicators.
- **Surface Tiers:** UI depth is managed through subtle variations of the neutral base. The main canvas is `#0F1117`, cards and sidebars use `#1A1D27`, and elevated "glass" floating elements use a semi-transparent layer of `#252936` with a background blur.
- **Status Semantic Colors:** 
    - Success: Emerald (#10B981)
    - Warning: Amber (#F59E0B)
    - Error: Rose (#F43F5E)
    - Info: Sky (#0EA5E9)

## Typography

This design system uses **Geist** for its technical precision and exceptional legibility in data-dense layouts. 

- **Wordmark Styling:** The product name is treated as a single unit where "Material" uses ExtraBold (800) and "Track" uses Regular (400) to create a clear visual hierarchy within the brand.
- **Scale:** High-density tables and sidebars primarily utilize `body-md` and `label-md` to maximize information density without sacrificing clarity.
- **Headlines:** Use `display-lg` sparingly for dashboard summaries; use `headline-md` for page titles within the fixed header.

## Layout & Spacing

The layout follows a strict **8px grid system**, ensuring consistent alignment across complex ERP modules.

- **Grid System:** A 12-column fluid grid is used for the main content area with 24px gutters. 
- **Sidebar & Header:** The sidebar is a collapsible vertical navigation that defaults to 260px. The top header is fixed at 64px height with a semi-transparent glass effect.
- **Density:** To accommodate ERP workflows, the "High-Density" mode reduces vertical padding in tables from 16px to 8px.
- **Breakpoints:**
    - Mobile (< 768px): Sidebar becomes an off-canvas drawer. 16px horizontal margins.
    - Tablet (768px - 1280px): Sidebar collapses to icon-only mode.
    - Desktop (> 1280px): Fixed sidebar with fluid content area.

## Elevation & Depth

Hierarchy is established through **Tonal Layers** and **Glassmorphism** rather than traditional heavy shadows.

- **Base Level:** The primary canvas background (#0F1117).
- **Surface Level:** Cards and containers use #1A1D27. They feature a 1px solid border (#FFFFFF10) to define edges in the dark environment.
- **Elevated Level:** Hovered cards and active modals use a background blur (20px) and a subtle "Soft Glow" shadow—a diffused indigo outer glow (#6366F1) with 15% opacity.
- **Interactive Elements:** Buttons and floating actions (like the AI assistant) use a linear-gradient fill with a 2px "inner-light" stroke at the top edge to simulate a physical edge catch.

## Shapes

The design system adopts a **Rounded** shape language to soften the industrial nature of ERP data.

- **Primary Cards:** 16px (`rounded-xl`) corner radius for major dashboard components and containers.
- **Interactive Elements:** 8px (`rounded-md`) for buttons, input fields, and checkboxes.
- **Status Badges & AI Assistant:** Pill-shaped (fully rounded) for distinct visual differentiation from data fields.
- **Visual Consistency:** Ensure that nested elements (like an input inside a card) have a slightly smaller radius (8px) than their parent (16px) to maintain geometric harmony.

## Components

### Buttons & Inputs
- **Primary Action:** Gradient fill with white text. Hover state includes a scale effect (1.02x) and an increased shadow glow.
- **Inputs:** Dark background (#12141C) with a subtle 1px border. On focus, the border transitions to the primary indigo color with a 4px soft outer glow.

### Sidebar (Collapsible Accordion)
- Active items are highlighted with an **Animated Pill**. A vertical gradient line appears on the left edge, and the background of the item receives a 10% opacity indigo tint. Groups use a chevron-down interaction with a 200ms ease-out height transition.

### High-Density Tables
- Rows feature a subtle zebra-striping using `#1A1D27` and `#141720`.
- **Status Badges:** Use low-opacity background tints (e.g., Success is 10% Emerald green) with high-contrast text and a left-aligned status dot.

### Floating AI Assistant
- Positioned in the bottom-right corner. It is a circular button featuring the brand gradient and a high-fidelity bot icon. On hover, it expands slightly and triggers a "breathing" glow animation.

### Motion Principles
- **Transitions:** All state changes (hover, focus, accordion expand) must use a 200ms `cubic-bezier(0.4, 0, 0.2, 1)` timing function.
- **Hover-Lift:** Cards should lift 4px on the Y-axis when hovered to provide tactile feedback.