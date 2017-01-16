-- Webpage profile 1.0

--
-- Default pages with translations
--

INSERT INTO `content` (`id`, `title`, `alias`, `publish_up`, `publish_down`, `published`, `associated_content_id`, `user_id`, `locale`) VALUES
(1, '404', '404', '2017-01-06 22:24:49', '0000-00-00 00:00:00', 1, NULL, 2, 'en_US'),
(2, '404', '404-fi', '2017-01-06 22:25:05', '0000-00-00 00:00:00', 1, 1, 2, 'fi_FI'),
(3, 'Home', 'home', '2017-01-06 22:26:29', '0000-00-00 00:00:00', 1, NULL, 2, 'en_US'),
(4, 'Etusivu', 'etusivu', '2017-01-06 22:26:36', '0000-00-00 00:00:00', 1, 3, 2, 'fi_FI');

--
-- Categories for default pages
--

INSERT INTO `content_category` (`id`, `content_id`, `category_id`) VALUES
(1, 1, 1),
(2, 2, 1),
(3, 3, 1),
(4, 4, 1);

--
-- Filed values for default pages
--

INSERT INTO `content_field_value` (`content_id`, `field_id`, `value`, `locale`, `ordering`) VALUES
(1, 1, '<p><strong>Error</strong></p><p>Page not found.</p>', 'en_US', 0),
(2, 1, '<p><strong>Virhe</strong></p><p>Sivua ei l&ouml;ydy.</p>', 'fi_FI', 0),
(3, 1, '<p>Homepage.</p>\r\n', 'en_US', 0),
(4, 1, '<p>Etusivu.</p>\r\n', 'fi_FI', 0),
(0, 2, '3', 'en_US', 0), -- Default front page
(0, 2, '4', 'fi_FI', 0), -- Default front page FI
(0, 3, '1', 'en_US', 0), -- 404 Page
(0, 3, '2', 'fi_FI', 0), -- 404 Page FI
(0, 4, 'Webvaloa', 'en_US', 1),
(0, 4, 'Webvaloa', 'fi_FI', 1),
(0, 5, ' - ', 'en_US', 1), -- Separator
(0, 5, ' - ', 'fi_FI', 1),
(0, 6, '1', 'en_US', 1), -- Use separator + page title
(0, 6, '1', 'fi_FI', 1);

--
-- Fields
--

INSERT INTO `field` (`id`, `field_group_id`, `name`, `translation`, `repeatable`, `type`, `settings`, `help_text`, `ordering`) VALUES
(2, 3, 'default_front_page', 'Default front page', 0, 'Articlepicker', '{"category":""}', 'The default or home page', 0),
(3, 3, 'default_404_page', '404 page', 0, 'Articlepicker', '{"category":""}', 'What article to load if alias isn\'t found', 0),
(4, 4, 'site_title', 'Default site title', 0, 'Text', NULL, 'What title to use as the default', 0),
(5, 4, 'site_title_separator', 'Title separator', 0, 'Text', NULL, 'Text or characters to separate page title from site title', 0),
(6, 4, 'site_title_page', 'Use page name in title', 0, 'Checkbox', NULL, 'Should we use the site title + separator + page title?', 0);

--
-- Field groups
--

INSERT INTO `field_group` (`id`, `name`, `translation`, `repeatable`, `global`) VALUES
(3, 'article_view', 'Article Settings', 0, 1),
(4, 'site_settings', 'Site Settings', 0, 1);

--
-- Enable plugins
--
INSERT INTO `plugin` (`plugin`, `system_plugin`, `blocked`, `ordering`) VALUES
('ErrorRedirect', 0, 0, 10),
('SettingsTemplateList', 0, 0, 10),
('PluginLanguageSwitcher', 0, 0, 10),
('PluginGlobalsView', 0, 0, 10),
('PluginArticleFieldView', 0, 0, 10);
