CREATE TABLE /*_*/questycaptcha (
  -- Primary key
  qcq_id int NOT NULL PRIMARY KEY AUTO_INCREMENT,

  -- The question text
  qcq_question text NOT NULL,

  -- Number of times the question has been correctly answered
  qcq_correct int unsigned NOT NULL default 0,

  -- Number of times the question has been incorrectly answered
  qcq_wrong int unsigned NOT NULL default 0
) /*$wgDBTableOptions*/;

CREATE TABLE /*_*/questycaptcha_answer (
  -- The key of the question that this answer corresponds to
  qca_id int unsigned NOT NULL default 0,

  -- The answer text
  qca_answer text NOT NULL
) /*$wgDBTableOptions*/;
