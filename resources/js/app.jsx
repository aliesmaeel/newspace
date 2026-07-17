import React, { useEffect, useMemo, useState } from "react";
import { createRoot } from "react-dom/client";
import { BrowserRouter, Link, NavLink, Route, Routes, useLocation } from "react-router-dom";
import { AuthProvider, useAuth } from "./AuthContext";
import { LoginPage, RegisterPage, RegistrationPopup } from "./AuthPages";
import { EventsPage, EventDetailPage } from "./EventPages";
import { BRAND_LOGO, BRAND_NAME } from "./brand";
import "./bootstrap";

const navItems = [
    { label: "About", to: "/" },
    // Hidden for now: { label: "Our Services", to: "/services" },
    { label: "SAKOUR Mission", to: "/mission" },
    { label: "The Three Journeys", to: "/programs" },
    { label: "Events", to: "/events" },
    { label: "Booking", to: "/booking" },
    { label: "Contact", to: "/contact" },
];

const services = [
    {
        title: "Leadership Coaching",
        body: "Unlock your leadership potential with personalized coaching sessions designed for business owners, family business leaders, and teams navigating growth.",
        bullets: ["Lead with clarity and purpose", "Catalyze growth and unlock potential", "Align your vision with meaningful goals"],
        image: "/assets/leadership-image.png",
    },
    {
        title: "Family Business Transformation Authority",
        body: "Navigate generational change with proven methods for succession, governance, and sustainable wealth creation.",
        bullets: ["Facilitate seamless transitions", "Strengthen governance", "Build wealth while preserving legacy"],
        image: "/assets/business-blueprint.png",
    },
    {
        title: "Full Business Blueprint Program",
        body: "Build and scale your business with practical frameworks across strategy, leadership, operations, and profitability.",
        bullets: ["Refine your vision", "Craft winning strategies", "Build a scalable model"],
        image: "/assets/it-consultation.png",
    },
];

const fallbackPrograms = [
    {
        slug: "twelve-weeks",
        title: "12 Weeks Commitment",
        description:
            "<p>Biweekly group coaching addressing the following areas:</p><ul><li>Idea</li><li>Brand</li><li>Productization/ Packages</li><li>Team &amp; Partners</li><li>Cash Flow</li><li>Systems</li></ul>",
        image_url: "/assets/program-12-weeks.png",
        price_cents: 0,
        billing_interval_months: null,
        price_label: "",
    },
    {
        slug: "six-months",
        title: "6 Months Commitment",
        description: "Biweekly group coaching with retreats, leadership game sessions, and wealth-building strategies.",
        image_url: "/assets/program-6-months.png",
        price_cents: 120000,
        billing_interval_months: 1,
        price_label: "£1,200.00 / month",
    },
    {
        slug: "one-year",
        title: "1 Year Commitment",
        description: "Platinum mastermind with retreats, coaching sessions, and global community benefits.",
        image_url: "/assets/program-1-year.png",
        price_cents: 240000,
        billing_interval_months: 12,
        price_label: "£2,400.00 / 12 months",
    },
];

function ContactInquiryForm() {
    const [formData, setFormData] = useState({
        firstName: "",
        lastName: "",
        email: "",
        phone: "",
        subject: "",
        question: "",
    });
    const [submitted, setSubmitted] = useState(false);

    function updateField(field, value) {
        setFormData((prev) => ({ ...prev, [field]: value }));
        setSubmitted(false);
    }

    function handleSubmit(event) {
        event.preventDefault();
        setSubmitted(true);
    }

    return (
        <form className="booking-form" onSubmit={handleSubmit}>
            <div className="booking-row">
                <input
                    type="text"
                    placeholder="First name"
                    value={formData.firstName}
                    onChange={(e) => updateField("firstName", e.target.value)}
                    required
                />
                <input
                    type="text"
                    placeholder="Last name"
                    value={formData.lastName}
                    onChange={(e) => updateField("lastName", e.target.value)}
                    required
                />
            </div>
            <input
                type="email"
                placeholder="Email"
                value={formData.email}
                onChange={(e) => updateField("email", e.target.value)}
                required
            />
            <input
                type="tel"
                placeholder="Phone"
                value={formData.phone}
                onChange={(e) => updateField("phone", e.target.value)}
                required
            />
            <input
                type="text"
                placeholder="Subject"
                value={formData.subject}
                onChange={(e) => updateField("subject", e.target.value)}
                required
            />
            <textarea
                placeholder="Question or comment"
                rows={6}
                value={formData.question}
                onChange={(e) => updateField("question", e.target.value)}
                required
            />
            {submitted && <p className="booking-success">Thank you. Your message has been received.</p>}
            <button type="submit" className="btn btn-primary cursor-pointer">
                Submit
            </button>
        </form>
    );
}

function EmailVerificationNotice() {
    const { user, resendVerificationEmail } = useAuth();
    const [message, setMessage] = useState("");
    const [error, setError] = useState("");
    const [sending, setSending] = useState(false);

    if (!user || user.email_verified) {
        return null;
    }

    async function handleResend() {
        setSending(true);
        setError("");
        setMessage("");
        try {
            const text = await resendVerificationEmail();
            setMessage(text);
        } catch (err) {
            setError(err.message);
        } finally {
            setSending(false);
        }
    }

    return (
        <div className="email-verify-banner container">
            <p>
                Please verify your email address. Check your inbox for the welcome email, or{" "}
                <button type="button" className="email-verify-banner-link" disabled={sending} onClick={handleResend}>
                    {sending ? "Sending…" : "resend verification email"}
                </button>
                .
            </p>
            {message && <p className="booking-success">{message}</p>}
            {error && <p className="booking-error">{error}</p>}
        </div>
    );
}

function Layout({ children }) {
    const location = useLocation();
    const { user, logout } = useAuth();

    return (
        <div className="page">
            <EmailVerificationNotice />
            <header className="topbar">
                <div className="container topbar-inner">
                    <a className="brand" href="/" aria-label={BRAND_NAME}>
                        <img src={BRAND_LOGO} alt={`${BRAND_NAME} logo`} />
                    </a>
                    <nav className="nav">
                        {navItems.map((item) => {
                            const key = typeof item.to === "string" ? item.to : `${item.label}-${item.to.pathname}${item.to.hash ?? ""}`;
                            return (
                                <NavLink
                                    key={key}
                                    to={item.to}
                                    className={({ isActive }) => {
                                        if (item.label === "About") {
                                            const active = location.pathname === "/" && !location.hash;
                                            return `nav-link ${active ? "active" : ""}`;
                                        }
                                        return `nav-link ${isActive ? "active" : ""}`;
                                    }}
                                >
                                    {item.label}
                                </NavLink>
                            );
                        })}
                        {user ? (
                            <>
                                <span className="nav-link nav-link-muted">Hi, {user.name.split(" ")[0]}</span>
                                <button type="button" className="nav-link nav-link-btn" onClick={() => logout()}>
                                    Log out
                                </button>
                            </>
                        ) : (
                            <>
                                <NavLink
                                    to="/login"
                                    className={({ isActive }) => `nav-link ${isActive ? "active" : ""}`}
                                >
                                    Log in
                                </NavLink>
                                <NavLink
                                    to="/register"
                                    className={({ isActive }) => `nav-link nav-link-register ${isActive ? "active" : ""}`}
                                >
                                    Register
                                </NavLink>
                            </>
                        )}
                    </nav>
                </div>
            </header>
            <main>{children}</main>
            <footer className="footer">
                <div className="container footer-inner">
                    <p>{BRAND_NAME}</p>
                    <p>Copyright {BRAND_NAME} 2024-2026.</p>
                </div>
            </footer>
        </div>
    );
}

function HomePage() {
    const location = useLocation();
    const { refreshUser } = useAuth();
    const query = useMemo(() => new URLSearchParams(location.search), [location.search]);
    const paymentResult = query.get("payment");
    const [showHomePopup, setShowHomePopup] = useState(false);
    const [homePopupMessage, setHomePopupMessage] = useState("");

    const verifiedResult = query.get("verified");

    useEffect(() => {
        if (paymentResult === "success") {
            setHomePopupMessage("Payment received. Your appointment is confirmed.");
            setShowHomePopup(true);
        } else if (paymentResult === "cancelled") {
            setHomePopupMessage("Payment was cancelled. Please book again when ready.");
            setShowHomePopup(true);
        } else if (verifiedResult === "1") {
            refreshUser();
            setHomePopupMessage("Your email has been verified. Thank you!");
            setShowHomePopup(true);
        } else if (verifiedResult === "already") {
            setHomePopupMessage("Your email was already verified.");
            setShowHomePopup(true);
        } else if (verifiedResult === "invalid") {
            setHomePopupMessage("This verification link is invalid or has expired.");
            setShowHomePopup(true);
        }
    }, [paymentResult, verifiedResult]);

    function closeHomePopup() {
        setShowHomePopup(false);
        const url = new URL(window.location.href);
        url.searchParams.delete("payment");
        url.searchParams.delete("session_id");
        url.searchParams.delete("verified");
        window.history.replaceState({}, "", `${url.pathname}${url.search}${url.hash}`);
    }

    return (
        <Layout>
            <section className="hero-wrap">
                <div className="hero container">
                    <p className="eyebrow">SAKOUR Family Enterprise — Legacy, Leadership, Future Readiness</p>
                    <h1>
                        Legacy is
                        <br />
                        a Verb.
                    </h1>
                    <p className="lead">
                        Three decades. Three continents. One mission: guiding family enterprises whose wealth means more than money —
                        through leadership, succession, and the future they&apos;re building.
                    </p>
                    <div className="hero-actions">
                        <Link className="btn btn-primary" to="/booking">
                            Start a Legacy Conversation
                        </Link>
                    </div>
                </div>
            </section>
            <section className="container section intro-section">
                <p className="eyebrow">About {BRAND_NAME}</p>
                <h2>
                    Family business is one of the most powerful forms of enterprise in the world — and one of the most personal.
                </h2>
                <p className="lead">
                    Behind every decision, there is a story.
                    <br />
                    Behind every transition, there is a family.
                    <br />
                    Behind every legacy, there is a next generation preparing to carry it forward.
                </p>
            </section>
            <section className="container section who-we-serve-section">
                <p className="eyebrow">Who We Serve</p>
                <h2>Founders, family business owners, and next-generation leaders.</h2>
                <p className="lead">
                    At SAKOUR Family Enterprise, we work alongside founders, family business owners, and next-generation leaders as
                    they navigate growth, governance, succession, leadership, and transformation — with clarity, not just complexity.
                </p>

                <div className="audiences-block">
                    <p className="eyebrow">Who We Work With</p>
                    <div className="audiences-grid">
                        <article className="card audience-card">
                            <span className="section-number">01</span>
                            <h3>Founders</h3>
                            <p>
                                You built something from nothing. Now you&apos;re thinking about what happens to it — and who it becomes
                                — without you at the center of every decision.
                            </p>
                        </article>
                        <article className="card audience-card">
                            <span className="section-number">02</span>
                            <h3>Family Business Owners</h3>
                            <p>
                                You&apos;re balancing the business, the family, and everything in between. You need clarity on
                                governance, roles, and what &quot;fair&quot; actually means for the next chapter.
                            </p>
                        </article>
                        <article className="card audience-card">
                            <span className="section-number">03</span>
                            <h3>Next-Generation Leaders</h3>
                            <p>
                                You&apos;re stepping into a legacy you didn&apos;t build, but are expected to carry. You want to lead in
                                your own way — without losing what made the business work.
                            </p>
                        </article>
                    </div>
                </div>

                <div className="belief-block">
                    <p className="eyebrow">Our Belief</p>
                    <p className="lead">
                        For us, family enterprise isn&apos;t only about wealth, ownership, or continuity. It&apos;s about legacy,
                        responsibility, identity, and relationships — and the courage to prepare the next generation to lead well in
                        the era of AI, while staying deeply connected to the values that hold the family together.
                    </p>
                </div>

                <div className="why-now-block">
                    <p className="eyebrow">Why It Matters Now</p>
                    <h3>Enduring family enterprises prepare deliberately.</h3>
                    <p>
                        Global research on family enterprise makes one thing clear: the businesses that endure are the ones that prepare
                        deliberately — and the next generation commits when it can see the business stands for something.
                    </p>
                    <div className="insights-grid">
                        <article className="insight">
                            <p>
                                Fewer than half of family business leaders have a formal succession plan in place — yet those who do
                                describe it as the single most important decision they ever made.
                            </p>
                        </article>
                        <article className="insight">
                            <p>
                                The next generation engages when the business has visible purpose and social impact — purpose, not
                                pressure, is what carries a legacy across generations.
                            </p>
                        </article>
                        <article className="insight">
                            <p>
                                Legacy is the connective tissue between a family&apos;s values, its business purpose, and what it hopes
                                to pass forward — a gift and a responsibility.
                            </p>
                        </article>
                    </div>
                    <p className="research-source">
                        Source: KPMG International &amp; STEP Project Global Consortium — &quot;Empowering the Future of Family
                        Business&quot; (Global Family Business Survey, 1,800+ businesses, 33 countries)
                    </p>
                </div>

                <div className="transform-block">
                    <p className="eyebrow">What We Help Families Do</p>
                    <div className="transform-grid">
                        <article className="card transform-card">
                            <span className="transform-from">Complexity</span>
                            <span className="transform-arrow" aria-hidden="true">&rarr;</span>
                            <span className="transform-to">Clarity</span>
                        </article>
                        <article className="card transform-card">
                            <span className="transform-from">Transition</span>
                            <span className="transform-arrow" aria-hidden="true">&rarr;</span>
                            <span className="transform-to">Alignment</span>
                        </article>
                        <article className="card transform-card">
                            <span className="transform-from">Inherited Responsibility</span>
                            <span className="transform-arrow" aria-hidden="true">&rarr;</span>
                            <span className="transform-to">Intentional Leadership</span>
                        </article>
                    </div>
                </div>

                <div className="process-block">
                    <p className="eyebrow">How We Work</p>
                    <h3>A process built for how families actually change.</h3>
                    <div className="process-grid">
                        <article className="process-step">
                            <span className="section-number">01</span>
                            <div>
                                <p className="process-label">Listen</p>
                                <h4>Understand the story</h4>
                                <p>
                                    Not just the business&apos;s numbers, but the relationships, history, and unspoken expectations
                                    behind them.
                                </p>
                            </div>
                        </article>
                        <article className="process-step">
                            <span className="section-number">02</span>
                            <div>
                                <p className="process-label">Clarify</p>
                                <h4>Name what&apos;s real</h4>
                                <p>
                                    Where alignment exists, where it doesn&apos;t, and what needs deciding before it becomes a crisis.
                                </p>
                            </div>
                        </article>
                        <article className="process-step">
                            <span className="section-number">03</span>
                            <div>
                                <p className="process-label">Guide</p>
                                <h4>Work alongside you</h4>
                                <p>
                                    Governance design, succession planning, and leadership development — at a pace your family can
                                    sustain.
                                </p>
                            </div>
                        </article>
                        <article className="process-step">
                            <span className="section-number">04</span>
                            <div>
                                <p className="process-label">Sustain</p>
                                <h4>Stay engaged</h4>
                                <p>
                                    Legacy isn&apos;t a one-time decision. We stay with you as your family and business evolve.
                                </p>
                            </div>
                        </article>
                    </div>
                </div>

                <div className="why-block">
                    <p className="eyebrow">Why SAKOUR</p>
                    <h3 className="why-tagline">Businesses don&apos;t grow until their leaders do.</h3>
                    <p>
                        Family enterprises rarely fail for lack of strategy. They fail when leadership, family alignment, and culture
                        can&apos;t carry the strategy they already have. So our work begins where every plan will ultimately succeed or
                        fail: with the leader — then moves outward to the family, the culture, and the enterprise.
                    </p>
                    <p>
                        Most advisors bring one lens — legal, financial, or governance. SAKOUR was built differently: from decades
                        inside the room where enterprise transformation actually happens, a rigorous formal grounding in family
                        business governance and succession, a hands-on coaching practice — and the lived experience of a family
                        business owner.
                    </p>
                    <p>
                        We don&apos;t bring a single playbook. What works for a third-generation Gulf conglomerate rarely works the
                        same way for a European family institution or an African family enterprise entering its next chapter. Real
                        guidance has to be earned across cultures, not templated.
                    </p>
                </div>

                <div className="regions-block">
                    <p className="eyebrow">Where We Work</p>
                    <p className="lead">
                        One practice, four worlds — and the cross-border families who span them. Because guidance has to be earned in
                        a culture, not imported into it.
                    </p>
                    <div className="regions-grid">
                        <article className="card region-card">
                            <h3>United Kingdom</h3>
                            <p>
                                Where SAKOUR is rooted — home of our UK retreats, the Edinburgh &amp; Glasgow Leadership Games, and our
                                Cambridge-grounded practice.
                            </p>
                        </article>
                        <article className="card region-card">
                            <h3>Europe</h3>
                            <p>
                                Multi-generational family institutions navigating succession, professionalization, and a next
                                generation that demands purpose.
                            </p>
                        </article>
                        <article className="card region-card">
                            <h3>Middle East</h3>
                            <p>
                                Three decades of C-level trust across the region — where family, faith, and enterprise have never been
                                separate things.
                            </p>
                        </article>
                        <article className="card region-card">
                            <h3>Indonesia &amp; Southeast Asia</h3>
                            <p>
                                Home of our royal retreat, and a rising generation of family enterprises bridging tradition and
                                transformation.
                            </p>
                        </article>
                    </div>
                </div>
            </section>
            <section className="container section founder-section">
                <div className="founder-photo" role="img" aria-label="Raouda Sakour" />
                <article className="card founder-card">
                    <p className="eyebrow">Our Founder</p>
                    <h3>Raouda Sakour</h3>
                    <p className="founder-role">Founder, {BRAND_NAME}</p>
                    <p className="founder-hook">Raouda doesn&apos;t just advise family businesses. She owns and leads one.</p>
                    <p>
                        As an owner of a family property business, the questions her clients carry are questions she lives with too —
                        how to grow what the family has built, how to be fair to both the business and the relationships behind it, and
                        how to prepare what comes next without losing what matters most.
                    </p>
                    <p>
                        She pairs that insider&apos;s understanding with an outsider&apos;s rigor: 26+ years inside some of the world&apos;s
                        leading technology organizations — Oracle, Accenture, Capgemini, and Cognizant — leading digital and cloud
                        transformation with C-level leaders across the Middle East, Europe, the UK, and Africa.
                    </p>
                    <p>
                        She completed the Cambridge Judge Business School Family Business Leadership Programme, and is a Certified John
                        Maxwell Coach and DISC Consultant since 2020 — currently in Maxwell Leadership&apos;s Executive Director Program —
                        as well as a Certified John Maxwell Youth Coach, a credential she carries into every next-generation engagement,
                        because preparing young leaders is not a service line for her. It&apos;s her heart.
                    </p>
                    <ul className="founder-credentials">
                        <li>Family Business Owner</li>
                        <li>Oracle · Accenture · Capgemini · Cognizant</li>
                        <li>Cambridge Judge — Family Business Leadership</li>
                        <li>Maxwell Coach · DISC Consultant · Youth Coach</li>
                    </ul>
                    <blockquote className="founder-quote">
                        <p>
                            A pilot is trained to see the whole landscape at once, to stay calm when visibility drops, and to always
                            have a flight plan. I bring the same discipline to family enterprises — I call it leadership with altitude.
                        </p>
                        <footer>Raouda Sakour — Pilot in training, Edinburgh</footer>
                    </blockquote>
                </article>
            </section>
            <section className="container section">
                <article className="card cta-card">
                    <p className="eyebrow">Get In Touch</p>
                    <h3>Start a conversation about your next chapter.</h3>
                    <p>Have questions, comments, or inquiries? We would love to hear from you and help map your growth path.</p>
                    <Link className="btn btn-primary" to="/contact">
                        Book a Discovery Call
                    </Link>
                </article>
            </section>
            {showHomePopup && (
                <div className="booking-success-overlay" role="dialog" aria-modal="true" aria-label="Payment update">
                    <div className="booking-success-modal">
                        <h3>{paymentResult === "success" ? "Success" : "Payment update"}</h3>
                        <p>{homePopupMessage}</p>
                        <button type="button" className="btn btn-primary" onClick={closeHomePopup}>
                            Close
                        </button>
                    </div>
                </div>
            )}
        </Layout>
    );
}

function MissionPage() {
    return (
        <Layout>
            <section className="impact-band mission-hero">
                <div className="container impact-inner">
                    <p className="eyebrow">The SAKOUR Mission</p>
                    <h2>One million futures.</h2>
                    <p>
                        SAKOUR Family Enterprise exists for a purpose larger than itself. Raouda&apos;s lifetime mission is to fund the
                        education of one million medical students around the world — young people with the calling to heal, and without
                        the means to get there.
                    </p>
                    <p>
                        It began in 2017, with her charitable foundation sponsoring medical students in Syria and supporting communities
                        across Africa. A quarter of everything SAKOUR earns serves this mission — which means every family we work with
                        becomes part of it. Every leader who grows, every succession that succeeds, every business that thrives sends
                        another young person toward medicine.
                    </p>
                    <p className="impact-closing">When your family business grows, another family&apos;s future grows with it.</p>
                </div>
            </section>
            <section className="container section">
                <article className="card cta-card">
                    <p className="eyebrow">Be Part of It</p>
                    <h3>Every engagement moves the mission forward.</h3>
                    <p>Start a conversation about your family enterprise — and become part of one million futures.</p>
                    <Link className="btn btn-primary" to="/booking">
                        Start a Legacy Conversation
                    </Link>
                </article>
            </section>
        </Layout>
    );
}

function ServicesPage() {
    return (
        <Layout>
            <section className="container section services-page">
                <p className="eyebrow">Our Services</p>
                <h2>Transformative experiences that inspire conscious leadership.</h2>
                <p className="lead">
                    At {BRAND_NAME}, we are dedicated to creating transformative experiences that inspire conscious leadership and
                    meaningful impact. Our services are designed to foster deep, authentic connections through personalized coaching,
                    thought-provoking workshops, and innovative events.
                </p>
                <p className="lead">
                    Raouda's unique approach blends wisdom, empathy, and strategic insight to help individuals and organizations
                    unlock their potential and make a lasting difference. Whether it's through intimate one-on-one sessions or dynamic
                    group settings, {BRAND_NAME} is a haven for growth, where each conversation ignites new possibilities for personal and
                    professional development.
                </p>

                <div className="services-offers">
                    <article className="card offer-card">
                        <div className="offer-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2L14.4 8.4L21 10.8L14.4 13.2L12 20L9.6 13.2L3 10.8L9.6 8.4L12 2Z" stroke="currentColor" strokeWidth="1.6" strokeLinejoin="round"/>
                                <path d="M14.8 9.2L19 5" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round"/>
                            </svg>
                        </div>
                        <p className="offer-duration">30 - 45 minutes</p>
                        <h3>Strategy Session</h3>
                        <p>Helps me understand if I can help or not. If I can help I need to know where.</p>
                    </article>

                    <article className="card offer-card">
                        <div className="offer-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 21s-7-4.6-9.4-9C.8 7 3.5 4 6.9 4c1.7 0 3.2.8 4.1 2c.9-1.2 2.4-2 4.1-2c3.4 0 6.1 3 4.3 8c-2.4 4.4-9.4 9-9.4 9Z" stroke="currentColor" strokeWidth="1.6" strokeLinejoin="round"/>
                                <path d="M7.6 13.1l1.9 1.9 5.2-5.2" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                            </svg>
                        </div>
                        <p className="offer-duration">6 months</p>
                        <h3>Raouda's coaching</h3>
                        <ul>
                            <li>Includes: two retreats</li>
                            <li>The Leadership Game</li>
                            <li>Wealth building strategies and more</li>
                        </ul>
                    </article>

                    <article className="card offer-card">
                        <div className="offer-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2l3 7 7 3-7 3-3 7-3-7-7-3 7-3 3-7Z" stroke="currentColor" strokeWidth="1.6" strokeLinejoin="round"/>
                                <path d="M8.5 12h7" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round"/>
                            </svg>
                        </div>
                        <p className="offer-duration">12-month programme</p>
                        <h3>12-month programme</h3>
                        <ul>
                            <li>Includes: four retreats</li>
                            <li>The Leadership Game</li>
                            <li>Wealth building strategies and more</li>
                            <li>Circle of Excellence Membership</li>
                        </ul>
                    </article>
                </div>

                <div className="services-programs-cta">
                    <Link className="btn btn-primary" to="/programs">
                        The Three Journeys
                    </Link>
                </div>

                <div className="stack services-list" aria-label="Service Areas">
                    {services.map((item) => (
                        <article className="card" key={item.title}>
                            <img src={item.image} alt={item.title} className="service-card-image" />
                            <h3>{item.title}</h3>
                            <p>{item.body}</p>
                            <ul>
                                {item.bullets.map((bullet) => (
                                    <li key={bullet}>{bullet}</li>
                                ))}
                            </ul>
                        </article>
                    ))}
                </div>
            </section>
        </Layout>
    );
}

function ProgramsPage() {
    const [programs, setPrograms] = useState(fallbackPrograms);

    useEffect(() => {
        let cancelled = false;

        async function fetchPrograms() {
            try {
                const response = await fetch("/api/programs");
                if (!response.ok) {
                    return;
                }
                const payload = await response.json();
                const items = Array.isArray(payload.programs) ? payload.programs : [];
                if (!cancelled && items.length > 0) {
                    setPrograms(items);
                }
            } catch {
                // Keep fallback programs when API is unavailable.
            }
        }

        fetchPrograms();

        return () => {
            cancelled = true;
        };
    }, []);

    return (
        <Layout>
            <section className="programs-hero">
                <div className="container">
                    <h1>The Three Journeys</h1>
                </div>
            </section>
            <section className="container section programs-intro">
                <p className="lead">
                    Every engagement begins with The Legacy Conversation — a complimentary, private conversation. From there, three
                    journeys — each one deeper than the last.
                </p>
                <div className="programs-stack">
                    {programs.map((program) => (
                        <article className="card program-card" key={program.slug}>
                            <div
                                className="program-image"
                                style={{
                                    backgroundImage: `linear-gradient(to bottom, rgba(12, 11, 10, 0.15), rgba(12, 11, 10, 0.4)), url("${program.image_url || "/assets/program-12-weeks.png"}")`,
                                }}
                            />
                            <div className="program-content">
                                {program.price_label && program.price_label.toLowerCase() !== "free" ? (
                                    <p className="duration">{program.price_label}</p>
                                ) : null}
                                <h3>{program.title}</h3>
                                <div
                                    className="program-details"
                                    dangerouslySetInnerHTML={{
                                        __html: program.description || "<p>Program details will be shared during booking.</p>",
                                    }}
                                />
                                <a className="btn btn-dark" href={`/booking?plan=${program.slug}`}>
                                    Book now
                                </a>
                            </div>
                        </article>
                    ))}
                </div>
            </section>
            <section className="programs-contact">
                <div className="container programs-contact-inner">
                    <p className="eyebrow">Get In Touch</p>
                    <h2>We would love to hear from you.</h2>
                    <p className="lead">
                        Send us a message with your question or comment. We will get back to you as soon as we can.
                    </p>
                    <article className="card booking-card contact-page-card lead">
                        <ContactInquiryForm />
                    </article>
                </div>
            </section>
        </Layout>
    );
}

/** Parse "9:00 AM" / "2:30 PM" on a calendar day in the user's local timezone (matches API slot strings). */
function parseBookingSlotLocal(date, slotLabel) {
    const match = /^(\d{1,2}):(\d{2})\s*(AM|PM)$/i.exec(String(slotLabel).trim());
    if (!match) {
        return null;
    }
    let hour = parseInt(match[1], 10);
    const minute = parseInt(match[2], 10);
    const mer = match[3].toUpperCase();
    if (mer === "PM" && hour !== 12) {
        hour += 12;
    }
    if (mer === "AM" && hour === 12) {
        hour = 0;
    }
    return new Date(date.getFullYear(), date.getMonth(), date.getDate(), hour, minute, 0, 0);
}

function isBookingSlotInPast(date, slotLabel) {
    const start = parseBookingSlotLocal(date, slotLabel);
    if (!start) {
        return false;
    }
    return start.getTime() < Date.now();
}

function BookingPage() {
    const location = useLocation();
    const query = useMemo(() => new URLSearchParams(location.search), [location.search]);
    const selectedProgramPlan = query.get("plan");
    const paymentResult = query.get("payment");
    const [programCatalog, setProgramCatalog] = useState(fallbackPrograms);
    const selectedProgram = useMemo(
        () => programCatalog.find((program) => program.slug === selectedProgramPlan) || null,
        [programCatalog, selectedProgramPlan]
    );
    const isPaidProgram = Boolean(selectedProgram && Number(selectedProgram.price_cents) >= 100);

    const timeSlots = useMemo(() => {
        const out = [];
        for (let mins = 10 * 60; mins <= 17 * 60; mins += 30) {
            const h24 = Math.floor(mins / 60);
            const m = mins % 60;
            const isPm = h24 >= 12;
            let h12 = h24 % 12;
            if (h12 === 0) {
                h12 = 12;
            }
            const mm = m === 0 ? "00" : "30";
            out.push(`${h12}:${mm} ${isPm ? "PM" : "AM"}`);
        }
        return out;
    }, []);

    const availableDates = useMemo(() => {
        const dates = [];
        const cursor = new Date();
        cursor.setHours(0, 0, 0, 0);
        while (dates.length < 14) {
            const dow = cursor.getDay();
            if (dow !== 0 && dow !== 6) {
                dates.push(new Date(cursor));
            }
            cursor.setDate(cursor.getDate() + 1);
        }
        return dates;
    }, []);

    const [selectedDate, setSelectedDate] = useState(availableDates[0]);
    const [selectedTime, setSelectedTime] = useState("");
    const [bookedSlots, setBookedSlots] = useState([]);
    const [unavailableSlots, setUnavailableSlots] = useState([]);
    const [loadingSlots, setLoadingSlots] = useState(false);
    const [submitError, setSubmitError] = useState("");
    const [submitLocked, setSubmitLocked] = useState(false);
    const [showSuccessPopup, setShowSuccessPopup] = useState(false);
    const [successPopupMessage, setSuccessPopupMessage] = useState("");
    const [hasShownPaymentPopup, setHasShownPaymentPopup] = useState(false);
    const [formData, setFormData] = useState({
        firstName: "",
        lastName: "",
        email: "",
        phone: "",
        message: "",
    });
    const [submitted, setSubmitted] = useState(false);
    const [clockTick, setClockTick] = useState(0);

    useEffect(() => {
        let cancelled = false;

        async function fetchPrograms() {
            try {
                const response = await fetch("/api/programs");
                if (!response.ok) {
                    return;
                }
                const payload = await response.json();
                const items = Array.isArray(payload.programs) ? payload.programs : [];
                if (!cancelled && items.length > 0) {
                    setProgramCatalog(items);
                }
            } catch {
                // Keep fallback programs when API is unavailable.
            }
        }

        fetchPrograms();

        return () => {
            cancelled = true;
        };
    }, []);

    useEffect(() => {
        const id = window.setInterval(() => setClockTick((n) => n + 1), 60000);
        return () => window.clearInterval(id);
    }, []);

    function updateField(field, value) {
        setFormData((prev) => ({ ...prev, [field]: value }));
        setSubmitted(false);
        setSubmitError("");
    }

    function selectedDateIso() {
        return selectedDate.toLocaleDateString("en-CA");
    }

    function resetBookingFields() {
        setFormData({
            firstName: "",
            lastName: "",
            email: "",
            phone: "",
            message: "",
        });
        setSelectedTime("");
        setSubmitted(false);
    }

    function openSuccessPopup(message) {
        setSuccessPopupMessage(message);
        setShowSuccessPopup(true);
    }

    useEffect(() => {
        let cancelled = false;
        async function fetchAvailability() {
            setLoadingSlots(true);
            try {
                const response = await fetch(`/api/bookings/availability?date=${selectedDateIso()}`);
                const data = await response.json();
                if (!cancelled) {
                    const booked = Array.isArray(data.booked) ? data.booked : [];
                    const unavailable = Array.isArray(data.unavailable) ? data.unavailable : [];
                    setBookedSlots(booked);
                    setUnavailableSlots(unavailable);
                }
            } catch {
                if (!cancelled) {
                    setBookedSlots([]);
                    setUnavailableSlots([]);
                }
            } finally {
                if (!cancelled) {
                    setLoadingSlots(false);
                }
            }
        }

        fetchAvailability();

        return () => {
            cancelled = true;
        };
    }, [selectedDate]);

    useEffect(() => {
        if (!selectedTime) {
            return;
        }
        if (
            bookedSlots.includes(selectedTime) ||
            unavailableSlots.includes(selectedTime) ||
            isBookingSlotInPast(selectedDate, selectedTime)
        ) {
            setSelectedTime("");
        }
    }, [selectedDate, selectedTime, bookedSlots, unavailableSlots, clockTick]);

    useEffect(() => {
        if (paymentResult === "success" && !hasShownPaymentPopup) {
            resetBookingFields();
            openSuccessPopup("Payment received. Your appointment is confirmed. Check your email for details.");
            setHasShownPaymentPopup(true);
        }
    }, [paymentResult, hasShownPaymentPopup]);

    async function handleSubmit(event) {
        event.preventDefault();
        if (submitLocked) {
            return;
        }
        setSubmitLocked(true);
        window.setTimeout(() => {
            setSubmitLocked(false);
        }, 3000);
        setSubmitError("");
        if (!selectedTime) {
            return;
        }
        try {
            const endpoint = isPaidProgram ? "/api/bookings/checkout-session" : "/api/bookings";
            const response = await fetch(endpoint, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    first_name: formData.firstName,
                    last_name: formData.lastName,
                    email: formData.email,
                    phone: formData.phone,
                    message: formData.message,
                    date: selectedDateIso(),
                    time: selectedTime,
                    program_plan_key: selectedProgramPlan,
                    return_base_url: window.location.origin,
                }),
            });

            if (response.status === 409) {
                setSubmitError("This time slot was just booked. Please choose another one.");
                setSelectedTime("");
                setSubmitted(false);
                return;
            }

            if (!response.ok) {
                setSubmitError("We could not complete your booking. Please try again.");
                setSubmitted(false);
                return;
            }

            if (isPaidProgram) {
                const payload = await response.json();
                if (payload.checkout_url) {
                    window.location.href = payload.checkout_url;
                    return;
                }
                setSubmitError("Could not start payment checkout. Please try again.");
                return;
            }

            setSubmitted(true);
            resetBookingFields();
            openSuccessPopup("Thanks! Your appointment is confirmed. Check your email for details.");
        } catch {
            setSubmitError("Something went wrong while submitting your booking.");
            setSubmitted(false);
        }
    }

    return (
        <Layout>
            <section className="container section">
                <p className="eyebrow">Booking</p>
                <h2>Choose a date, time slot, and submit your details.</h2>
                {isPaidProgram && (
                    <p className="lead">
                        You selected <strong>{selectedProgram?.title}</strong> ({selectedProgram?.price_label}). After
                        submitting this form, you will be redirected to Stripe to start your subscription.
                    </p>
                )}
                {paymentResult === "cancelled" && (
                    <p className="booking-error">Payment was cancelled. You can submit again to retry checkout.</p>
                )}
                <article className="card booking-card booking-layout">
                    <div className="calendar-panel">
                        <h3>Select a Date</h3>
                        <p className="booking-dates-note">Weekdays only — Saturday and Sunday are not available.</p>
                        <div className="date-grid">
                            {availableDates.map((date) => {
                                const isActive = date.toDateString() === selectedDate.toDateString();
                                const label = date.toLocaleDateString("en-US", { month: "short", day: "numeric" });
                                const weekday = date.toLocaleDateString("en-US", { weekday: "short" });

                                return (
                                    <button
                                        key={date.toISOString()}
                                        type="button"
                                        className={`date-pill ${isActive ? "active" : ""}`}
                                        onClick={() => {
                                            setSelectedDate(date);
                                            setSelectedTime("");
                                            setSubmitted(false);
                                        }}
                                    >
                                        <span>{weekday}</span>
                                        <strong>{label}</strong>
                                    </button>
                                );
                            })}
                        </div>

                        <h3>Available Time Slots</h3>
                        <div className="slot-grid">
                            {timeSlots.map((slot) => {
                                const isBooked = bookedSlots.includes(slot);
                                const isUnavailable = unavailableSlots.includes(slot);
                                const isPast = isBookingSlotInPast(selectedDate, slot);
                                const isDisabled = isBooked || isUnavailable || isPast || loadingSlots;
                                return (
                                    <button
                                        key={slot}
                                        type="button"
                                        className={`slot-pill ${selectedTime === slot ? "active" : ""} ${isBooked || isUnavailable || isPast ? "disabled" : ""}`}
                                        disabled={isDisabled}
                                        onClick={() => {
                                            if (isDisabled) {
                                                return;
                                            }
                                            setSelectedTime(slot);
                                            setSubmitted(false);
                                            setSubmitError("");
                                        }}
                                    >
                                        {slot}
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    <div className="booking-form-panel">
                        <p className="booking-meta">
                            <strong>Selected:</strong>{" "}
                            {selectedDate.toLocaleDateString("en-US", {
                                weekday: "long",
                                month: "short",
                                day: "numeric",
                                year: "numeric",
                            })}{" "}
                            {selectedTime ? `at ${selectedTime}` : "(choose a time slot)"}
                        </p>

                        <form className="booking-form" onSubmit={handleSubmit}>
                            <div className="booking-row">
                                <input
                                    type="text"
                                    placeholder="First name"
                                    value={formData.firstName}
                                    onChange={(e) => updateField("firstName", e.target.value)}
                                    required
                                />
                                <input
                                    type="text"
                                    placeholder="Last name"
                                    value={formData.lastName}
                                    onChange={(e) => updateField("lastName", e.target.value)}
                                    required
                                />
                            </div>
                            <input
                                type="email"
                                placeholder="Email"
                                value={formData.email}
                                onChange={(e) => updateField("email", e.target.value)}
                                required
                            />
                            <input
                                type="tel"
                                placeholder="Phone"
                                value={formData.phone}
                                onChange={(e) => updateField("phone", e.target.value)}
                                required
                            />
                            <textarea
                                placeholder="Message"
                                rows={5}
                                value={formData.message}
                                onChange={(e) => updateField("message", e.target.value)}
                                required
                            />
                            {!selectedTime && <p className="booking-error">Please choose a time slot before submitting.</p>}
                            {submitError && <p className="booking-error">{submitError}</p>}
                            {submitted && <p className="booking-success">Thanks! Your appointment is confirmed. Check your email for details.</p>}
                            <button type="submit" className="btn btn-primary cursor-pointer" disabled={submitLocked}>
                                {isPaidProgram ? "Proceed to Payment" : "Submit"}
                            </button>
                        </form>
                    </div>
                </article>

                {showSuccessPopup && (
                    <div className="booking-success-overlay" role="dialog" aria-modal="true" aria-label="Booking success">
                        <div className="booking-success-modal">
                            <h3>Success</h3>
                            <p>{successPopupMessage}</p>
                            <button
                                type="button"
                                className="btn btn-primary cursor-pointer"
                                onClick={() => {
                                    setShowSuccessPopup(false);
                                }}
                            >
                                Close
                            </button>
                        </div>
                    </div>
                )}
            </section>
        </Layout>
    );
}

function ContactPage() {
    return (
        <Layout>
            <section className="container section">
                <p className="eyebrow">Contact Us</p>
                <h2>We would love to hear from you.</h2>
                <p className="lead">
                    Send us a message with your question or comment. We will get back to you as soon as we can.
                </p>
                <article className="card booking-card contact-page-card">
                    <ContactInquiryForm />
                </article>
            </section>
        </Layout>
    );
}

function App() {
    return (
        <AuthProvider>
            <BrowserRouter>
                <RegistrationPopup />
                <Routes>
                    <Route path="/" element={<HomePage />} />
                    {/* Hidden for now: <Route path="/services" element={<ServicesPage />} /> */}
                    <Route path="/mission" element={<MissionPage />} />
                    <Route path="/programs" element={<ProgramsPage />} />
                    <Route path="/events" element={<EventsPage Layout={Layout} />} />
                    <Route path="/events/:slug" element={<EventDetailPage Layout={Layout} />} />
                    <Route path="/booking" element={<BookingPage />} />
                    <Route path="/contact" element={<ContactPage />} />
                    <Route path="/login" element={<LoginPage Layout={Layout} />} />
                    <Route path="/register" element={<RegisterPage Layout={Layout} />} />
                </Routes>
            </BrowserRouter>
        </AuthProvider>
    );
}

createRoot(document.getElementById("app")).render(
    <React.StrictMode>
        <App />
    </React.StrictMode>
);
