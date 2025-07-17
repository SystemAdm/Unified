<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Mail\ContactFormMail;

class ContactController extends Controller
{
    /**
     * Handle the contact form submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submit(Request $request)
    {
        // Validate the form data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'recaptcha' => 'required|string',
        ]);

        // Verify reCAPTCHA v3
        $recaptchaResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $validated['recaptcha'],
            'remoteip' => $request->ip(),
        ]);

        $recaptchaData = $recaptchaResponse->json();

        // Check if reCAPTCHA verification was successful
        if (!isset($recaptchaData['success']) || !$recaptchaData['success']) {
            return redirect()->back()
                ->withInput($request->except('recaptcha'))
                ->withErrors(['recaptcha' => 'reCAPTCHA verification failed. Please try again.']);
        }

        // For reCAPTCHA v3, check the score (0.0 to 1.0, where 1.0 is very likely a good interaction)
        if (!isset($recaptchaData['score']) || $recaptchaData['score'] < 0.5) {
            return redirect()->back()
                ->withInput($request->except('recaptcha'))
                ->withErrors(['recaptcha' => 'Your submission was flagged as potentially suspicious. Please try again.']);
        }

        // Verify the action matches what we expect
        if (!isset($recaptchaData['action']) || $recaptchaData['action'] !== 'contact') {
            return redirect()->back()
                ->withInput($request->except('recaptcha'))
                ->withErrors(['recaptcha' => 'Invalid reCAPTCHA action. Please try again.']);
        }

        // Remove recaptcha from validated data before sending email
        unset($validated['recaptcha']);

        // Send an email with the contact form data
        Mail::to(env('MAIL_FROM_ADDRESS', 'hello@example.com'))->send(new ContactFormMail($validated));

        return redirect()->back()->with('success', 'Your message has been sent successfully!');
    }
}
