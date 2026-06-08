import React, { useEffect, useState } from "react";
import { Link, useNavigate, useSearchParams } from "react-router-dom";
import { apiFetch } from "./apiClient";
import { useAuth } from "./AuthContext";
import { BRAND_NAME } from "./brand";

export function LoginPage({ Layout }) {
    const { login, user } = useAuth();
    const navigate = useNavigate();
    const [searchParams] = useSearchParams();
    const redirect = searchParams.get("redirect") || "/";
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [error, setError] = useState("");

    useEffect(() => {
        if (user) {
            navigate(redirect);
        }
    }, [user, navigate, redirect]);

    async function handleSubmit(e) {
        e.preventDefault();
        setError("");
        try {
            await login(email, password);
            navigate(redirect);
        } catch (err) {
            setError(err.message);
        }
    }

    return (
        <Layout>
            <section className="container section">
                <p className="eyebrow">Login</p>
                <h2>Welcome back</h2>
                <article className="card booking-card contact-page-card">
                    <form className="booking-form" onSubmit={handleSubmit}>
                        <input type="email" placeholder="Email" value={email} onChange={(e) => setEmail(e.target.value)} required />
                        <input
                            type="password"
                            placeholder="Password"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            required
                        />
                        {error && <p className="booking-error">{error}</p>}
                        <button type="submit" className="btn btn-primary">
                            Log in
                        </button>
                    </form>
                    <p className="lead" style={{ marginTop: "1rem" }}>
                        No account? <Link to={`/register?redirect=${encodeURIComponent(redirect)}`}>Register</Link>
                    </p>
                </article>
            </section>
        </Layout>
    );
}

export function RegisterPage({ Layout }) {
    const { register, user } = useAuth();
    const navigate = useNavigate();
    const [searchParams] = useSearchParams();
    const redirect = searchParams.get("redirect") || "/";
    const [interestOptions, setInterestOptions] = useState([]);
    const [form, setForm] = useState({
        name: "",
        email: "",
        password: "",
        password_confirmation: "",
        phone: "",
        interest_option_id: "",
        hear_about_us: "",
    });
    const [error, setError] = useState("");
    const [success, setSuccess] = useState("");

    useEffect(() => {
        if (user?.email_verified) {
            navigate(redirect);
        }
    }, [user, navigate, redirect]);

    useEffect(() => {
        async function load() {
            const { data } = await apiFetch("/api/interest-options");
            setInterestOptions(Array.isArray(data?.options) ? data.options : []);
        }
        load();
    }, []);

    function update(field, value) {
        setForm((prev) => ({ ...prev, [field]: value }));
    }

    const emptyForm = {
        name: "",
        email: "",
        password: "",
        password_confirmation: "",
        phone: "",
        interest_option_id: "",
        hear_about_us: "",
    };

    async function handleSubmit(e) {
        e.preventDefault();
        setError("");
        try {
            const data = await register({
                ...form,
                interest_option_id: form.interest_option_id || null,
                phone: form.phone || null,
            });
            const msg = data?.message || "Welcome! Please check your email to verify your account.";
            setForm(emptyForm);
            setSuccess(data?.email_sent === false ? `${msg} You can use “Resend verification email” after signing in.` : msg);
        } catch (err) {
            setError(err.message);
        }
    }

    return (
        <Layout>
            <section className="container section">
                <p className="eyebrow">Register</p>
                <h2>Create your account</h2>
                {success ? (
                    <article className="card booking-card contact-page-card">
                        <p className="booking-success">{success}</p>
                        <p className="lead">Open the link in your email to verify your address, then continue using the site.</p>
                        <Link className="btn btn-primary" to={redirect}>
                            Continue
                        </Link>
                    </article>
                ) : (
                <article className="card booking-card contact-page-card">
                    <form className="booking-form" onSubmit={handleSubmit}>
                        <input type="text" placeholder="Full name" value={form.name} onChange={(e) => update("name", e.target.value)} required />
                        <input type="email" placeholder="Email" value={form.email} onChange={(e) => update("email", e.target.value)} required />
                        <input type="tel" placeholder="Phone (optional)" value={form.phone} onChange={(e) => update("phone", e.target.value)} />
                        <select
                            value={form.interest_option_id}
                            onChange={(e) => update("interest_option_id", e.target.value)}
                            className="booking-select"
                        >
                            <option value="">What are you interested in?</option>
                            {interestOptions.map((opt) => (
                                <option key={opt.id} value={opt.id}>
                                    {opt.label}
                                </option>
                            ))}
                        </select>
                        <input
                            type="text"
                            placeholder="How did you hear about us?"
                            value={form.hear_about_us}
                            onChange={(e) => update("hear_about_us", e.target.value)}
                        />
                        <input
                            type="password"
                            placeholder="Password"
                            value={form.password}
                            onChange={(e) => update("password", e.target.value)}
                            required
                        />
                        <input
                            type="password"
                            placeholder="Confirm password"
                            value={form.password_confirmation}
                            onChange={(e) => update("password_confirmation", e.target.value)}
                            required
                        />
                        {error && <p className="booking-error">{error}</p>}
                        <button type="submit" className="btn btn-primary">
                            Register
                        </button>
                    </form>
                    <p className="lead" style={{ marginTop: "1rem" }}>
                        Already have an account? <Link to={`/login?redirect=${encodeURIComponent(redirect)}`}>Log in</Link>
                    </p>
                </article>
                )}
            </section>
        </Layout>
    );
}

export function RegistrationPopup() {
    const { user, register } = useAuth();
    const [visible, setVisible] = useState(false);
    const [interestOptions, setInterestOptions] = useState([]);
    const [form, setForm] = useState({
        name: "",
        email: "",
        password: "",
        password_confirmation: "",
        phone: "",
        interest_option_id: "",
        hear_about_us: "",
    });
    const [error, setError] = useState("");
    const [success, setSuccess] = useState(false);

    useEffect(() => {
        if (user) {
            return;
        }
        if (localStorage.getItem("registration_popup_dismissed") === "1") {
            return;
        }
        const timer = window.setTimeout(() => setVisible(true), 4000);
        return () => window.clearTimeout(timer);
    }, [user]);

    useEffect(() => {
        if (!visible) {
            return;
        }
        async function load() {
            const { data } = await apiFetch("/api/interest-options");
            setInterestOptions(Array.isArray(data?.options) ? data.options : []);
        }
        load();
    }, [visible]);

    function dismiss() {
        localStorage.setItem("registration_popup_dismissed", "1");
        setVisible(false);
    }

    async function handleSubmit(e) {
        e.preventDefault();
        setError("");
        try {
            await register({
                ...form,
                interest_option_id: form.interest_option_id || null,
                phone: form.phone || null,
            });
            setForm({
                name: "",
                email: "",
                password: "",
                password_confirmation: "",
                phone: "",
                interest_option_id: "",
                hear_about_us: "",
            });
            setSuccess(true);
            setError("");
            window.setTimeout(() => {
                dismiss();
            }, 5000);
        } catch (err) {
            setError(err.message);
        }
    }

    if (!visible || (user && !success)) {
        return null;
    }

    return (
        <div className="booking-success-overlay" role="dialog" aria-modal="true" aria-label="Register">
            <div className="booking-success-modal" style={{ maxWidth: "28rem", textAlign: "left" }}>
                <h3>Join {BRAND_NAME}</h3>
                <p>Create a free account to book programs and attend events.</p>
                {success ? (
                    <>
                        <p className="booking-success">Registration successful!</p>
                        <p>Welcome! Please check your email for a verification link, then log in to continue.</p>
                    </>
                ) : (
                    <form className="booking-form" onSubmit={handleSubmit}>
                        <input type="text" placeholder="Full name" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required />
                        <input type="email" placeholder="Email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} required />
                        <input type="tel" placeholder="Phone (optional)" value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} />
                        <select
                            value={form.interest_option_id}
                            onChange={(e) => setForm({ ...form, interest_option_id: e.target.value })}
                            className="booking-select"
                        >
                            <option value="">What are you interested in?</option>
                            {interestOptions.map((opt) => (
                                <option key={opt.id} value={opt.id}>
                                    {opt.label}
                                </option>
                            ))}
                        </select>
                        <input
                            type="text"
                            placeholder="How did you hear about us?"
                            value={form.hear_about_us}
                            onChange={(e) => setForm({ ...form, hear_about_us: e.target.value })}
                        />
                        <input
                            type="password"
                            placeholder="Password"
                            value={form.password}
                            onChange={(e) => setForm({ ...form, password: e.target.value })}
                            required
                        />
                        <input
                            type="password"
                            placeholder="Confirm password"
                            value={form.password_confirmation}
                            onChange={(e) => setForm({ ...form, password_confirmation: e.target.value })}
                            required
                        />
                        {error && <p className="booking-error">{error}</p>}
                        <button type="submit" className="btn btn-primary">
                            Register
                        </button>
                    </form>
                )}
                <button type="button" className="btn btn-ghost" style={{ marginTop: "0.75rem" }} onClick={dismiss}>
                    Not now
                </button>
            </div>
        </div>
    );
}
