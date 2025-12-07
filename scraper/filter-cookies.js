module.exports = function filter(raw) {
  return raw
    .split(';')
    .map((v) => v.trim())
    .filter(
      (v) =>
        v.startsWith('nlbi_') ||
        v.startsWith('visid_incap_') ||
        v.startsWith('incap_ses_') ||
        v.startsWith('reese84') ||
        v.startsWith('g2user') ||
        v.startsWith('G2JSE') ||
        v.startsWith('anonymousCrmId')
    )
    .join('; ');
};
