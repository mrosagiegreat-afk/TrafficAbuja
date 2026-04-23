class Address1stHeader extends HTMLElement {
  connectedCallback() {
    if (this.dataset.rendered === "true") return;

    this.dataset.rendered = "true";

    if (!document.querySelector('link[data-address1st-header-font="true"]')) {
      const preconnectFonts = document.createElement("link");
      preconnectFonts.rel = "preconnect";
      preconnectFonts.href = "https://fonts.googleapis.com";
      preconnectFonts.setAttribute("data-address1st-header-font", "true");
      document.head.appendChild(preconnectFonts);

      const preconnectStatic = document.createElement("link");
      preconnectStatic.rel = "preconnect";
      preconnectStatic.href = "https://fonts.gstatic.com";
      preconnectStatic.crossOrigin = "anonymous";
      preconnectStatic.setAttribute("data-address1st-header-font", "true");
      document.head.appendChild(preconnectStatic);

      const fontLink = document.createElement("link");
      fontLink.rel = "stylesheet";
      fontLink.href = "https://fonts.googleapis.com/css2?family=Pacifico&display=swap";
      fontLink.setAttribute("data-address1st-header-font", "true");
      document.head.appendChild(fontLink);
    }

    const logoSrc = this.getAttribute("logo-src") || "logo.png";
    const brand = this.getAttribute("brand") || "Address1st";
    const pageTitle = this.getAttribute("page-title") || brand;
    const personalizeLabel = this.getAttribute("personalize-label") || "Personalize";
    const aboutLabel = this.getAttribute("about-label") || "FAQ";

    document.title = pageTitle;

    this.innerHTML = `
      <style>
        .address1st-header-shell {
          width: 100%;
          padding: 1rem 4rem 0;
          position: relative;
          z-index: 1;
        }

        .address1st-header-inner {
          display: flex;
          justify-content: space-between;
          align-items: center;
          gap: 2rem;
          padding: 0.5rem 0;
          margin: 0 auto 0.9rem;
        }

        .address1st-header-logo {
          display: inline-flex;
          flex-direction: column;
          align-items: center;
          flex-shrink: 0;
          gap: 0.15rem;
        }

        .address1st-header-logo img {
          display: block;
          width: auto;
          height: 44px;
          object-fit: contain;
        }

        .address1st-header-wordmark {
          flex: 0 1 auto;
          text-align: left;
          font-family: "Pacifico", cursive;
          font-size: clamp(1.8rem, 4vw, 3.3rem);
          line-height: 1;
          color: #64748b;
          letter-spacing: 0.01em;
          white-space: nowrap;
          margin-right: auto;
        }

        .address1st-header-nav {
          display: flex;
          align-items: center;
          gap: 0.9rem;
          flex-wrap: wrap;
          justify-content: flex-end;
          margin-left: auto;
        }

        .address1st-header-action {
          width: auto;
          min-height: 40px;
          padding: 0.55rem 0.9rem;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          border: none;
          background: transparent;
          box-shadow: none;
          transition: color 0.2s ease, background-color 0.2s ease;
          color: #2c2c2c;
          cursor: pointer;
          border-radius: 999px;
          font: inherit;
          font-size: 0.92rem;
          font-weight: 500;
          letter-spacing: 0.01em;
          gap: 0.5rem;
          white-space: nowrap;
        }

        .address1st-header-action:hover,
        .address1st-header-action:focus-visible {
          background: rgba(15, 23, 42, 0.06);
          outline: none;
        }

        .address1st-header-action--personalize:hover,
        .address1st-header-action--personalize:focus-visible {
          color: #1da1f2;
        }

        .address1st-header-action--about:hover,
        .address1st-header-action--about:focus-visible {
          color: #5865f2;
        }

        .address1st-header-icon {
          display: inline-flex;
          width: 18px;
          height: 18px;
          align-items: center;
          justify-content: center;
          flex-shrink: 0;
        }

        .address1st-header-icon svg {
          width: 100%;
          height: 100%;
          display: block;
          fill: currentColor;
        }

        @media (max-width: 768px) {
          .address1st-header-shell {
            padding: 0.85rem 2rem 0;
          }

          .address1st-header-inner {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 0.75rem;
          }

          .address1st-header-wordmark {
            flex: 1 1 auto;
            text-align: center;
            font-size: clamp(1.2rem, 6vw, 2rem);
            margin-right: 0;
          }

          .address1st-header-nav {
            gap: 0.6rem;
            width: auto;
            justify-content: flex-end;
            align-self: center;
          }

          .address1st-header-action {
            min-height: 32px;
            width: 32px;
            min-width: 32px;
            padding: 0.35rem;
            gap: 0;
          }

          .address1st-header-icon {
            width: 14px;
            height: 14px;
          }

          .address1st-header-label {
            display: none;
          }

          .address1st-header-action--about .address1st-header-label {
            display: inline;
          }
        }
      </style>

      <div class="address1st-header-shell">
        <div class="address1st-header-inner">
          <div class="address1st-header-logo">
            <img src="${logoSrc}" alt="Address1st logo">
          </div>
          <div class="address1st-header-wordmark" aria-label="${brand} brand name">${brand}</div>
          <div class="address1st-header-nav" aria-label="Header actions">
            <button class="address1st-header-action address1st-header-action--personalize" type="button" aria-label="${personalizeLabel}">
              <span class="address1st-header-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" focusable="false" aria-hidden="true">
                  <path d="M10 4a2 2 0 1 1 4 0v1.08a7.002 7.002 0 0 1 2.61 1.08l.77-.77a2 2 0 1 1 2.83 2.83l-.77.77A7.002 7.002 0 0 1 20 11h1a2 2 0 1 1 0 4h-1.08a7.002 7.002 0 0 1-1.08 2.61l.77.77a2 2 0 1 1-2.83 2.83l-.77-.77A7.002 7.002 0 0 1 13 21v1a2 2 0 1 1-4 0v-1.08a7.002 7.002 0 0 1-2.61-1.08l-.77.77a2 2 0 1 1-2.83-2.83l.77-.77A7.002 7.002 0 0 1 4 13H3a2 2 0 1 1 0-4h1.08a7.002 7.002 0 0 1 1.08-2.61l-.77-.77a2 2 0 1 1 2.83-2.83l.77.77A7.002 7.002 0 0 1 11 5.08V4Zm2 5a3 3 0 1 0 0 6a3 3 0 0 0 0-6Z"></path>
                </svg>
              </span>
              <span class="address1st-header-label">${personalizeLabel}</span>
            </button>
            <button class="address1st-header-action address1st-header-action--about" type="button" aria-label="${aboutLabel}">
              <span class="address1st-header-label">${aboutLabel}</span>
            </button>
          </div>
        </div>
      </div>
    `;
  }
}

customElements.define("address1st-header", Address1stHeader);
