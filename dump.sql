--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


--
-- Name: ltree; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS ltree WITH SCHEMA public;


--
-- Name: EXTENSION ltree; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION ltree IS 'data type for hierarchical tree-like structures';


SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: edges; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE edges (
    start integer NOT NULL,
    "end" integer NOT NULL,
    pos integer
);


ALTER TABLE edges OWNER TO postgres;

--
-- Name: nodes; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE nodes (
    id integer NOT NULL,
    name character varying(100)
);


ALTER TABLE nodes OWNER TO postgres;

--
-- Name: nodes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE nodes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE nodes_id_seq OWNER TO postgres;

--
-- Name: nodes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE nodes_id_seq OWNED BY nodes.id;


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY nodes ALTER COLUMN id SET DEFAULT nextval('nodes_id_seq'::regclass);


--
-- Data for Name: edges; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY edges (start, "end", pos) FROM stdin;
1	4	1
2	4	1
2	5	2
3	5	1
4	6	1
4	7	2
5	7	1
5	8	2
7	9	1
7	10	2
7	11	2
\.


--
-- Data for Name: nodes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY nodes (id, name) FROM stdin;
1	A
2	B
3	C
4	D
5	E
6	F
7	G
8	H
9	I
10	J
11	K
\.


--
-- Name: nodes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('nodes_id_seq', 11, true);


--
-- Name: edges_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY edges
    ADD CONSTRAINT edges_pkey PRIMARY KEY (start, "end");


--
-- Name: nodes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY nodes
    ADD CONSTRAINT nodes_pkey PRIMARY KEY (id);


--
-- Name: nodes_end_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY edges
    ADD CONSTRAINT nodes_end_id_fk FOREIGN KEY ("end") REFERENCES nodes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: nodes_start_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY edges
    ADD CONSTRAINT nodes_start_id_fk FOREIGN KEY (start) REFERENCES nodes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;
GRANT ALL ON SCHEMA public TO graph;


--
-- Name: edges; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE edges FROM PUBLIC;
REVOKE ALL ON TABLE edges FROM postgres;
GRANT ALL ON TABLE edges TO postgres;
GRANT ALL ON TABLE edges TO graph;


--
-- Name: nodes; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE nodes FROM PUBLIC;
REVOKE ALL ON TABLE nodes FROM postgres;
GRANT ALL ON TABLE nodes TO postgres;
GRANT ALL ON TABLE nodes TO graph;


--
-- Name: nodes_id_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON SEQUENCE nodes_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE nodes_id_seq FROM postgres;
GRANT ALL ON SEQUENCE nodes_id_seq TO postgres;
GRANT ALL ON SEQUENCE nodes_id_seq TO graph;


--
-- PostgreSQL database dump complete
--

