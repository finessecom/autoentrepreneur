# DESIGN.md — L'Auto-Entrepreneur

## 1. Objective
Someone landing on this page should feel: "This tool was built for people like me, by people who understand Moroccan auto-entrepreneurship." The quality bar is clarity over decoration — every element earns its space. The page converts by being specific, not by being loud.

## 2. Product Context
- **What it does:** Manages invoices, declarations, clients, and fiscal obligations for Moroccan auto-entrepreneurs
- **Who it's for:** Moroccan auto-entrepreneurs who need to create documents, track payments, and file quarterly declarations
- **Adjacent brands (feel like these):** Mercury (fintech clarity), Notion (clean utility), Doctolib (French professional service)
- **Distant brand (do not feel like this):** Canva (playful/creative) — this is a fiscal tool, not a design tool
- **Cultural register:** Professional with warmth — serious enough for fiscal matters, approachable enough for non-accountants

## 3. Visual Foundations

### 3a. Color
- **Neutral scale:** `--n-50: #F8F9FA, --n-100: #F1F3F5, --n-200: #E9ECEF, --n-300: #DEE2E6, --n-400: #CED4DA, --n-500: #ADB5BD, --n-600: #6C757D, --n-700: #495057, --n-800: #343A40, --n-900: #212529`
- **Accent primary:** `--accent: #1B4D89` (deep blue from the banner logo)
- **Accent secondary:** `--accent-warm: #E63946` (red from the banner, used sparingly)
- **Background:** `--bg: #FFFFFF` (clean white, no gradients)
- **Usage rules:** Blue for primary CTA and section anchors. Red for one emphasis moment per section. Never as background fill.

### 3b. Typography
- **Display face:** `Inter 700` (headings, tight letter-spacing -0.02em)
- **Body face:** `Inter 400` (body text, line-height 1.6)
- **Fallback stack:** `Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif`
- **Type scale:** `14 / 16 / 18 / 24 / 32 / 48 / 64` (modular, ratio ~1.25)
- **Weight discipline:** 400 for body, 600 for labels/UI, 700 for headings only. No light weights.

### 3c. Spacing & rhythm
- **Base unit:** 8px
- **Spacing scale:** `8, 16, 24, 32, 48, 64, 96, 128 px`
- **Section padding:** ≥ 96px on desktop, ≥ 64px on mobile
- **Content max-width:** 1140px centered

### 3d. Component seeds
- **Button:** Two variants — primary (blue fill, white text, 8px radius) and ghost (blue border, blue text). Height 48px. One primary CTA per section.
- **Card / container:** No cards in the traditional sense. Content flows as typography. Where containers are needed: 1px border, 0px radius, white background.
- **Iconography:** Font Awesome, 20px, used sparingly.

## 4. Accessibility
- **Text contrast:** body 4.5:1 min against white, large text 3:1 min
- **Motion:** reduced-motion respected, no animations that convey information
- **Focus indicators:** 2px blue outline with 2px offset
- **Alt text policy:** descriptive for informational images, empty for decorative

## 5. Voice & Tone
- **Register:** Conversational-professional — like a knowledgeable friend explaining fiscal obligations
- **Sentence rhythm:** Short sentences. One idea per sentence.
- **Words this brand uses:** "gérer", "déclarer", "simplifier", "automatiser"
- **Words this brand refuses:** "seamlessly", "elevate", "journey", "unlock", "delight", "solution", "plateforme"
- **Address:** "vous" — direct, professional

## 6. Implementation Practices
- **Token format:** CSS custom properties
- **Component library:** Bootstrap 5.3 + custom overrides
- **Image treatment:** Real product screenshots or no images. No stock photos. The banner image is the brand anchor.
- **Grid system:** Bootstrap 12-col, max-width 1140px
- **Motion:** minimal — only hover states on buttons (transition 150ms ease)

## 7. Anti-Patterns
- **No gradient hero.** The brand has a real banner image — use it or use solid color.
- **No rounded-16px card grids.** Features are presented as typography with subtle borders.
- **No emoji decoration.** Section anchors are numbers or typographic labels.
- **No "trust logos" strip.** This is a solo-user tool, not an enterprise platform.
- **No CTA in every section.** One primary CTA in the hero. The rest informs.

## 8. Decision-Making
1. **Clarity over conversion.** Trust matters more than a single signup.
2. **Specificity over generality.** Every sentence should be about THIS tool for THESE users.
3. **Moroccan professional context.** Bilingual (FR/AR) is a feature, not an afterthought.
4. **One accent per screen.** Blue is the primary. Red appears once for emphasis.

## 9. Workflow
1. Write the headline — the one sentence that makes someone want to read the second
2. Build the hero: left = headline + supporting copy, right = auth form
3. Write the features section — specific capabilities, not generic benefits
4. Apply color and type from the DESIGN.md
5. Check against anti-slop patterns
6. Test at 375px and 1440px
7. Verify French copy reads naturally, not translated
