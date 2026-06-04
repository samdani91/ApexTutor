/**
 * Helpers for extracting structured error information from Axios errors.
 *
 * Usage:
 *   const { extractMessage, extractFieldErrors, mapFieldErrors } = useApiError()
 *
 *   try { ... } catch (err) {
 *     mapFieldErrors(err, fieldErr)           // populates reactive fieldErr object
 *     toast.error(extractMessage(err))
 *   }
 */
export function useApiError() {
  /**
   * Return the API error message or a safe fallback.
   */
  function extractMessage(err, fallback = 'An unexpected error occurred.') {
    return err?.response?.data?.message ?? fallback
  }

  /**
   * Return the Laravel validation errors map ({ field: [messages] }).
   */
  function extractFieldErrors(err) {
    return err?.response?.data?.errors ?? {}
  }

  /**
   * Populate a reactive field-error object from a 422 response.
   * Only maps keys that already exist in the target object.
   */
  function mapFieldErrors(err, target) {
    const errors = extractFieldErrors(err)
    for (const [key, messages] of Object.entries(errors)) {
      if (key in target) {
        target[key] = Array.isArray(messages) ? messages[0] : messages
      }
    }
  }

  /**
   * Clear all fields in a reactive field-error object.
   */
  function clearFieldErrors(target) {
    for (const key of Object.keys(target)) {
      target[key] = ''
    }
  }

  return { extractMessage, extractFieldErrors, mapFieldErrors, clearFieldErrors }
}
