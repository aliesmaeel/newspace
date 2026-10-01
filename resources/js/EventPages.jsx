import React, { useEffect, useState } from "react";
import { Link, useNavigate, useParams, useSearchParams } from "react-router-dom";
import { apiFetch } from "./apiClient";
import { useAuth } from "./AuthContext";

function formatSessionDate(value) {
    if (!value) {
        return "";
    }

    return new Date(value).toLocaleString("en-GB", {
        dateStyle: "medium",
        timeStyle: "short",
    });
}

export function EventsPage({ Layout }) {
    const [events, setEvents] = useState([]);

    useEffect(() => {
        async function load() {
            const { data } = await apiFetch("/api/events");
            setEvents(Array.isArray(data?.events) ? data.events : []);
        }
        load();
    }, []);

    return (
        <Layout>
            <section className="container section">
                <p className="eyebrow">Events</p>
                <h2>Upcoming events</h2>
                <p className="lead">Browse events and choose a date and location that works for you.</p>
                <div className={`events-stack${events.length === 1 ? " events-stack--single" : ""}`}>
                    {events.map((event) => (
                        <article className="card program-card" key={event.slug}>
                            {event.image_url ? (
                                <div
                                    className="program-image"
                                    style={{
                                        backgroundImage: `linear-gradient(to bottom, rgba(12, 11, 10, 0.15), rgba(12, 11, 10, 0.4)), url("${event.image_url}")`,
                                    }}
                                />
                            ) : null}
                            <div className="program-content">
                                {event.summary_label ? <p className="duration">{event.summary_label}</p> : null}
                                <h3>{event.title}</h3>
                                {event.description ? (
                                    <div
                                        className="program-details event-card-description rich-content"
                                        dangerouslySetInnerHTML={{ __html: event.description }}
                                    />
                                ) : null}
                                <Link className="btn btn-dark" to={`/events/${event.slug}`}>
                                    View event
                                </Link>
                            </div>
                        </article>
                    ))}
                    {events.length === 0 && <p className="lead">No upcoming events yet.</p>}
                </div>
            </section>
        </Layout>
    );
}

function OccurrenceCard({
    event,
    occurrence,
    user,
    slug,
    firstTimeFreeEligible,
    submittingId,
    onRegister,
}) {
    const [promoCode, setPromoCode] = useState("");
    const navigate = useNavigate();
    const registered = occurrence.user_registration?.status === "confirmed";
    const paid = occurrence.user_registration?.payment_status === "paid";
    const isVirtual = occurrence.location_type === "virtual";
    const submitting = submittingId === occurrence.id;

    return (
        <article className="card booking-card event-occurrence-card">
            <div className="event-occurrence-header">
                <h3>{occurrence.label || occurrence.display_label || occurrence.location_label}</h3>
                <p className="duration">{occurrence.price_label}</p>
            </div>
            <p className="lead" style={{ marginBottom: "0.5rem" }}>
                <span className={`event-location-badge event-location-badge--${occurrence.location_type || "physical"}`}>
                    {occurrence.location_label}
                </span>
                <span className="event-meta-sep"> · </span>
                {formatSessionDate(occurrence.starts_at)}
            </p>
            {occurrence.location_type === "physical" && occurrence.address ? (
                <p className="lead">
                    <strong>Location:</strong> {occurrence.address}
                    {occurrence.map_url ? (
                        <>
                            {" "}
                            <a href={occurrence.map_url} target="_blank" rel="noopener noreferrer">
                                View on map
                            </a>
                        </>
                    ) : null}
                </p>
            ) : null}
            {isVirtual && occurrence.virtual_link ? (
                <p className="lead">
                    <strong>Meeting link:</strong>{" "}
                    <a href={occurrence.virtual_link} target="_blank" rel="noopener noreferrer" style={{ color: "#0057ff" }}>
                        Join online
                    </a>
                </p>
            ) : null}
            {isVirtual && !occurrence.virtual_link && occurrence.has_virtual_meeting ? (
                <p className="lead event-meeting-hint">
                    {registered && !paid
                        ? "Your payment is being confirmed. Refresh this page in a moment, or contact support if the meeting link does not appear."
                        : "The meeting link will appear here after you register and complete payment (or use a free registration)."}
                </p>
            ) : null}

            {registered ? (
                <p className="booking-success">You are registered for this session.</p>
            ) : (
                <>
                    {!user && (
                        <p className="lead">
                            <Link to={`/register?redirect=/events/${slug}`}>Register</Link> or{" "}
                            <Link to={`/login?redirect=/events/${slug}`}>log in</Link> to attend.
                        </p>
                    )}
                    {firstTimeFreeEligible || Number(occurrence.price_cents) < 100 ? (
                        <p className="lead">
                            Experience the {event.event_type_name || "event"} for the first time, with our compliments
                        </p>
                    ) : null}
                    <div className="booking-form" style={{ marginTop: "0.75rem" }}>
                        <input
                            type="text"
                            placeholder="Promo code (optional)"
                            value={promoCode}
                            onChange={(e) => setPromoCode(e.target.value)}
                        />
                        <button
                            type="button"
                            className="btn btn-primary"
                            disabled={submitting}
                            onClick={() => {
                                if (!user) {
                                    navigate(`/register?redirect=/events/${slug}`);
                                    return;
                                }
                                onRegister(occurrence.id, promoCode);
                            }}
                        >
                            {submitting ? "Please wait…" : user ? "Register for this session" : "Register / log in to attend"}
                        </button>
                    </div>
                </>
            )}
        </article>
    );
}

export function EventDetailPage({ Layout }) {
    const { slug } = useParams();
    const [searchParams] = useSearchParams();
    const { user, loading: authLoading } = useAuth();
    const [event, setEvent] = useState(null);
    const [error, setError] = useState("");
    const [message, setMessage] = useState("");
    const [submittingId, setSubmittingId] = useState(null);

    const loadEvent = React.useCallback(async () => {
        const { data } = await apiFetch(`/api/events/${slug}`);
        setEvent(data?.event ?? null);
    }, [slug]);

    useEffect(() => {
        if (authLoading) {
            return;
        }
        loadEvent();
    }, [authLoading, loadEvent]);

    useEffect(() => {
        if (searchParams.get("registration") === "success") {
            setMessage("You are registered for this session!");
            if (!authLoading) {
                loadEvent();
            }
        } else if (searchParams.get("registration") === "cancelled") {
            setError("Payment was cancelled. You can register again when ready.");
        }
    }, [searchParams, authLoading, loadEvent]);

    async function handleRegister(occurrenceId, promoCode) {
        setSubmittingId(occurrenceId);
        setError("");
        setMessage("");
        try {
            const { response, data } = await apiFetch(`/api/events/${slug}/register`, {
                method: "POST",
                body: JSON.stringify({
                    occurrence_id: occurrenceId,
                    promo_code: promoCode || null,
                    return_base_url: window.location.origin,
                }),
            });
            if (!response.ok) {
                throw new Error(data?.message || "Could not register.");
            }
            if (data.checkout_url) {
                window.location.href = data.checkout_url;
                return;
            }
            setMessage(data.message || "You are registered!");
            await loadEvent();
        } catch (e) {
            setError(e.message);
        } finally {
            setSubmittingId(null);
        }
    }

    if (!event) {
        return (
            <Layout>
                <section className="container section">
                    <p className="lead">Loading event…</p>
                </section>
            </Layout>
        );
    }

    const occurrences = Array.isArray(event.occurrences) ? event.occurrences : [];
    const highlightedOccurrenceId = searchParams.get("occurrence");

    return (
        <Layout>
            <section className="container section">
                <p className="eyebrow">Event</p>
                <h2>{event.title}</h2>
                {event.image_url ? (
                    <img src={event.image_url} alt={event.title} className="service-card-image" style={{ marginBottom: "1rem" }} />
                ) : null}
                {event.description ? (
                    <div className="program-details rich-content" dangerouslySetInnerHTML={{ __html: event.description }} />
                ) : null}

                {message && <p className="booking-success">{message}</p>}
                {error && <p className="booking-error">{error}</p>}

                <h3 style={{ marginTop: "2rem", marginBottom: "0.75rem" }}>Dates &amp; locations</h3>
                {occurrences.length === 0 ? (
                    <p className="lead">No upcoming sessions for this event yet.</p>
                ) : (
                    <div className="event-occurrences-stack">
                        {occurrences.map((occurrence) => (
                            <div
                                key={occurrence.id}
                                id={`occurrence-${occurrence.id}`}
                                className={
                                    String(highlightedOccurrenceId) === String(occurrence.id)
                                        ? "event-occurrence-highlight"
                                        : undefined
                                }
                            >
                                <OccurrenceCard
                                    event={event}
                                    occurrence={occurrence}
                                    user={user}
                                    slug={slug}
                                    firstTimeFreeEligible={event.first_time_free_eligible}
                                    submittingId={submittingId}
                                    onRegister={handleRegister}
                                />
                            </div>
                        ))}
                    </div>
                )}
            </section>
        </Layout>
    );
}
