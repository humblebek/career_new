@extends('layouts.guest')

@section('title', 'Privacy Policy')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">

        {{-- Header --}}
        <div class="mb-10">
            <a href="{{ route('home') }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                &larr; Back to Home
            </a>
            <h1 class="mt-4 text-4xl font-extrabold text-gray-900">Privacy Policy</h1>
            <p class="mt-2 text-sm text-gray-500">Effective date: {{ \Carbon\Carbon::now()->format('d F Y') }} &nbsp;|&nbsp; Last updated: {{ \Carbon\Carbon::now()->format('d F Y') }}</p>
        </div>

        <div class="bg-white shadow-sm rounded-2xl p-8 space-y-10 text-gray-700 leading-relaxed">

            {{-- 1. Who we are --}}
            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">1. Who We Are</h2>
                <p>
                    <strong>{{ config('app.name', 'CareerPath') }}</strong> ("we", "us", "our") is a career
                    assessment platform that helps students discover suitable career paths through guided tests.
                    This Privacy Policy explains what personal data we collect when you use our platform, why we
                    collect it, how long we keep it, and what rights you have under the
                    <strong>General Data Protection Regulation (GDPR)</strong> (EU) 2016/679.
                </p>
                <p class="mt-2">
                    For any privacy-related queries, contact us at:
                    <a href="mailto:privacy@careerpath.example.com" class="text-primary-600 hover:underline">privacy@careerpath.example.com</a>.
                </p>
            </section>

            {{-- 2. Data we collect --}}
            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">2. Personal Data We Collect</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-gray-100 text-gray-800">
                            <tr>
                                <th class="text-left px-4 py-3 font-semibold">Category</th>
                                <th class="text-left px-4 py-3 font-semibold">Data</th>
                                <th class="text-left px-4 py-3 font-semibold">Purpose</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr>
                                <td class="px-4 py-3 font-medium">Account data</td>
                                <td class="px-4 py-3">Name, email address, hashed password, secret verification word</td>
                                <td class="px-4 py-3">Account creation and two-factor authentication</td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-3 font-medium">Assessment data</td>
                                <td class="px-4 py-3">Answers to career test questions (including free-text responses)</td>
                                <td class="px-4 py-3">Calculating your career match result</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium">Result data</td>
                                <td class="px-4 py-3">Career match percentages, category scores, recommended career</td>
                                <td class="px-4 py-3">Showing and storing your assessment results</td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-3 font-medium">Usage data</td>
                                <td class="px-4 py-3">Session identifiers, timestamps of test attempts</td>
                                <td class="px-4 py-3">Platform security and test history</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-sm text-gray-500">We do <strong>not</strong> collect payment information, precise location data, or any special-category data (e.g. health, race, religion) as defined in GDPR Article 9.</p>
            </section>

            {{-- 3. Legal basis --}}
            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">3. Legal Basis for Processing</h2>
                <ul class="list-disc pl-5 space-y-2">
                    <li><strong>Contract performance (Art. 6(1)(b)):</strong> Processing your account and assessment data is necessary to provide the service you signed up for.</li>
                    <li><strong>Legitimate interests (Art. 6(1)(f)):</strong> Session data and login timestamps are processed to detect abuse and keep the platform secure.</li>
                    <li><strong>Consent (Art. 6(1)(a)):</strong> Where we ask for explicit consent (e.g. future marketing communications), you may withdraw it at any time.</li>
                </ul>
            </section>

            {{-- 4. Data retention --}}
            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">4. Data Retention</h2>
                <ul class="list-disc pl-5 space-y-2">
                    <li><strong>Account data:</strong> Retained for as long as your account is active. Deleted within 30 days of account deletion.</li>
                    <li><strong>Assessment answers &amp; results:</strong> Retained for the lifetime of your account so you can review past results. Deleted with your account.</li>
                    <li><strong>Session data:</strong> Purged automatically after session expiry (typically 2 hours of inactivity).</li>
                </ul>
            </section>

            {{-- 5. Data sharing --}}
            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">5. Data Sharing and Third Parties</h2>
                <p>We do <strong>not</strong> sell, rent, or share your personal data with third-party advertisers or data brokers.</p>
                <p class="mt-2">We may share data only in the following limited circumstances:</p>
                <ul class="list-disc pl-5 mt-2 space-y-2">
                    <li><strong>Hosting provider:</strong> Our infrastructure provider processes data on our behalf under a GDPR-compliant Data Processing Agreement.</li>
                    <li><strong>Legal obligation:</strong> If required by law or a valid court order.</li>
                </ul>
                <p class="mt-2">If we transfer data outside the EEA, we ensure appropriate safeguards are in place (e.g. Standard Contractual Clauses).</p>
            </section>

            {{-- 6. Your rights --}}
            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">6. Your Rights Under GDPR</h2>
                <p class="mb-3">You have the following rights regarding your personal data:</p>
                <ul class="list-disc pl-5 space-y-2">
                    <li><strong>Right of access (Art. 15):</strong> Request a copy of the data we hold about you.</li>
                    <li><strong>Right to rectification (Art. 16):</strong> Correct inaccurate or incomplete data via your Profile settings.</li>
                    <li><strong>Right to erasure (Art. 17):</strong> Delete your account and all associated data from your Profile settings, or by contacting us.</li>
                    <li><strong>Right to restriction (Art. 18):</strong> Ask us to restrict processing of your data in certain circumstances.</li>
                    <li><strong>Right to data portability (Art. 20):</strong> Receive your data in a machine-readable format. Contact us to make this request.</li>
                    <li><strong>Right to object (Art. 21):</strong> Object to processing based on legitimate interests.</li>
                    <li><strong>Right to withdraw consent:</strong> Where processing is based on consent, withdraw it at any time without affecting prior processing.</li>
                </ul>
                <p class="mt-3">
                    To exercise any of these rights, email us at
                    <a href="mailto:privacy@careerpath.example.com" class="text-primary-600 hover:underline">privacy@careerpath.example.com</a>.
                    We will respond within <strong>30 days</strong>. You also have the right to lodge a complaint with your national
                    supervisory authority (e.g. the ICO in the UK, or your local EU DPA).
                </p>
            </section>

            {{-- 7. Cookies --}}
            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">7. Cookies and Session Storage</h2>
                <p>We use a single session cookie (<code class="bg-gray-100 px-1 rounded text-sm">{{ config('session.cookie', 'laravel_session') }}</code>) that is strictly necessary to keep you logged in. We do not use tracking or advertising cookies.</p>
            </section>

            {{-- 8. Security --}}
            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">8. Security</h2>
                <p>
                    Passwords are stored as bcrypt hashes and are never stored in plain text. All communication is
                    encrypted in transit via HTTPS. Access to production data is restricted to authorised
                    personnel only. We apply two-factor authentication (secret word verification) to user accounts.
                </p>
            </section>

            {{-- 9. Children --}}
            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">9. Children's Privacy</h2>
                <p>
                    Our platform is not directed at children under the age of 16. If you believe a child under
                    16 has provided us with personal data without parental consent, please contact us and we will
                    delete it promptly.
                </p>
            </section>

            {{-- 10. Changes --}}
            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">10. Changes to This Policy</h2>
                <p>
                    We may update this policy from time to time. Material changes will be communicated by
                    posting a notice on the platform or by email. Continued use of the platform after the
                    effective date constitutes acceptance of the updated policy.
                </p>
            </section>

        </div>

        <p class="text-center text-xs text-gray-400 mt-8">
            &copy; {{ date('Y') }} {{ config('app.name', 'CareerPath') }}. All rights reserved.
        </p>
    </div>
</div>
@endsection